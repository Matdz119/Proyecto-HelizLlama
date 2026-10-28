<?php
/**
 * Endpoint AJAX del popup del asistente HelizLlama.
 *
 * Etapa placeholder: responde con un FAQ fijo por rol, sin backend de IA.
 * Cuando exista el orquestador, este archivo pasa a reenviar la pregunta a
 * /v1/ask; los controles de seguridad de aquí siguen siendo los mismos y por
 * eso se escriben ahora y no después.
 */

define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true); // Que un error de PHP no escupa rutas ni SQL al navegador.

require(__DIR__ . '/../../config.php');

/** Longitud máxima de una pregunta. */
const HELIZLLAMA_MAX_PREGUNTA = 1000;

// Solo POST: evita que una pregunta quede registrada en logs de acceso o en el
// historial del navegador, y descarta peticiones disparadas desde un <img src>.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new \moodle_exception('invalidrequest', 'error');
}

$courseid = required_param('courseid', PARAM_INT);
$question = required_param('question', PARAM_TEXT);

$course = get_course($courseid);

// require_login con el curso valida además la matrícula: tener la capacidad en
// un contexto superior no basta para consultar sobre un curso ajeno.
require_login($course, false);
require_sesskey();

$context = context_course::instance($course->id);
require_capability('local/helizllama:use', $context);

// Un invitado no tiene identidad estable ni matrícula real.
if (isguestuser()) {
    throw new \moodle_exception('noguest');
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!\local_helizllama\local\rate_limiter::permitir($USER->id)) {
    http_response_code(429);
    echo json_encode(['error' => get_string('ratelimited', 'local_helizllama')]);
    exit;
}

$question = trim($question);

if ($question === '') {
    echo json_encode(['answer' => '']);
    exit;
}

if (core_text::strlen($question) > HELIZLLAMA_MAX_PREGUNTA) {
    http_response_code(400);
    echo json_encode(['error' => get_string('questiontoolong', 'local_helizllama')]);
    exit;
}

\local_helizllama\event\question_asked::create([
    'context' => $context,
    'courseid' => $course->id,
])->trigger();

$isteacher = has_capability('local/helizllama:viewteacherfaq', $context);
$answer = \local_helizllama\local\faq_service::answer($question, $isteacher);

echo json_encode(['answer' => $answer]);
