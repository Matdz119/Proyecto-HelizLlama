<?php
defined('MOODLE_INTERNAL') || die();

class block_helizllama extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_helizllama');
    }

    public function applicable_formats() {
        return [
            'course-view' => true,
            'site'        => true,
            'my'          => true,
        ];
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return false;
    }

    public function get_content() {
        global $PAGE, $COURSE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id ?: SITEID);
        if (!has_capability('local/helizllama:use', $context)) {
            $this->content->text = '';
            return $this->content;
        }

        $isteacher = has_capability('local/helizllama:viewteacherfaq', $context);
        $roleindicator = $isteacher
            ? get_string('roleindicator_teacher', 'block_helizllama')
            : get_string('roleindicator_student', 'block_helizllama');

        $this->content->text = html_writer::div(
            html_writer::tag('button',
                get_string('opentitle', 'block_helizllama'),
                [
                    'type' => 'button',
                    'id' => 'block_helizllama_open_btn',
                    'class' => 'btn btn-primary btn-block',
                ]
            ) . html_writer::div($roleindicator, 'small text-muted mt-1'),
            'block_helizllama_wrapper'
        );

        $PAGE->requires->js_call_amd('block_helizllama/chat', 'init', [[
            'courseid' => $COURSE->id ?: SITEID,
            'isteacher' => $isteacher,
            'sesskey' => sesskey(),
        ]]);

        return $this->content;
    }
}
