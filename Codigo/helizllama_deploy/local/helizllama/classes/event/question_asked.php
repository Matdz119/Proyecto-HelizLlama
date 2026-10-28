<?php
namespace local_helizllama\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Se dispara cuando un usuario hace una pregunta al asistente.
 *
 * Deja rastro en el log estándar de Moodle, que es donde un administrador
 * académico va a buscar si hay que auditar el uso del asistente. No se guarda
 * el texto de la pregunta en el log: eso vive en la base del orquestador, que
 * tiene políticas de retención y de aislamiento por usuario.
 */
class question_asked extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    public static function get_name() {
        return get_string('event_question_asked', 'local_helizllama');
    }

    public function get_description() {
        return "El usuario con id '{$this->userid}' hizo una pregunta al asistente " .
               "HelizLlama en el curso con id '{$this->courseid}'.";
    }
}
