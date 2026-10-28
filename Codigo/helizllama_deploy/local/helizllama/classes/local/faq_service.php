<?php
namespace local_helizllama\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Placeholder question-answering service.
 *
 * No AI/LLM is connected yet (that is Sprint 2-3 of the thesis). This class only
 * matches the question against a small set of canned, role-specific answers and,
 * when nothing matches, returns the same "no tengo información" style fallback
 * that the real RAG pipeline will use later (RF-05: avoid making things up).
 */
class faq_service {

    /**
     * Canned answers for students: list of [keywords[], answer].
     */
    protected static function student_faq(): array {
        return [
            [
                'keywords' => ['entregar', 'entrega', 'tarea', 'subir archivo'],
                'answer' => 'Para entregar una tarea: entra a la actividad "Tarea" dentro del curso, ' .
                    'pulsa "Agregar entrega", sube tu archivo o escribe el texto pedido y guarda los cambios.',
            ],
            [
                'keywords' => ['calificacion', 'calificaciones', 'nota', 'notas'],
                'answer' => 'Tus calificaciones están en el menú del curso, sección "Calificaciones" ' .
                    '(o desde tu perfil, "Informe de calificaciones").',
            ],
            [
                'keywords' => ['inscrib', 'matricul', 'unirme al curso'],
                'answer' => 'Para inscribirte en un curso necesitas la clave de matriculación que te da tu ' .
                    'docente, o pedirle que te inscriba manualmente desde "Participantes".',
            ],
            [
                'keywords' => ['foro', 'participar', 'publicar mensaje'],
                'answer' => 'En los foros del curso puedes responder pulsando "Responder" debajo del mensaje, ' .
                    'o crear un tema nuevo con "Agregar un nuevo tema de discusión".',
            ],
        ];
    }

    /**
     * Canned answers for teachers/managers: list of [keywords[], answer].
     */
    protected static function teacher_faq(): array {
        return [
            [
                'keywords' => ['crear tarea', 'agregar tarea', 'nueva tarea'],
                'answer' => 'Para crear una tarea: activa la edición del curso, "Añadir una actividad o ' .
                    'recurso" → "Tarea", configura fecha de entrega y guarda.',
            ],
            [
                'keywords' => ['configuracion del curso', 'editar curso', 'ajustes del curso'],
                'answer' => 'La configuración del curso está en el engranaje de "Ajustes" dentro del propio curso.',
            ],
            [
                'keywords' => ['reporte', 'informe de actividad', 'estadisticas', 'registro'],
                'answer' => 'Los reportes de actividad del curso están en "Informes" dentro de la ' .
                    'administración del curso. El reporte propio de HelizLlama (uso del asistente) llegará ' .
                    'en una fase posterior de la tesis (HU-06).',
            ],
            [
                'keywords' => ['indexar', 'indexacion', 'reindexar'],
                'answer' => 'La indexación automática del contenido del curso para el asistente con IA ' .
                    'todavía no está activa; por ahora esto es solo un panel de respuestas de prueba.',
            ],
        ];
    }

    /**
     * Returns the best matching canned answer for a question, or the fallback
     * "no information" string if nothing matches.
     *
     * @param string $question free text question from the user.
     * @param bool $isteacher whether the caller should see teacher-oriented answers.
     * @return string the answer to display.
     */
    public static function answer(string $question, bool $isteacher): string {
        $normalized = self::normalize($question);

        $faq = $isteacher ? self::teacher_faq() : self::student_faq();

        foreach ($faq as $entry) {
            foreach ($entry['keywords'] as $keyword) {
                if (strpos($normalized, self::normalize($keyword)) !== false) {
                    return $entry['answer'];
                }
            }
        }

        return get_string('fallback_answer', 'local_helizllama');
    }

    /**
     * Lowercases and strips accents so keyword matching is not picky about tildes.
     */
    protected static function normalize(string $text): string {
        $text = \core_text::strtolower($text);
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ];
        return strtr($text, $replacements);
    }
}
