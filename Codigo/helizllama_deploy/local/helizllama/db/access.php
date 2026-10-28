<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Puede usar el asistente (estudiantes, docentes, managers).
    'local/helizllama:use' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => [
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ],
    ],
    // Marca quién recibe las respuestas "de docente" (más administrativas).
    'local/helizllama:viewteacherfaq' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => [
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ],
    ],
];
