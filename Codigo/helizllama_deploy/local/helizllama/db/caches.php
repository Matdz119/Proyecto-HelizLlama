<?php
defined('MOODLE_INTERNAL') || die();

$definitions = [
    // Contador de peticiones por usuario para el límite de tasa.
    'ratelimit' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => false,
        'ttl' => 120,
    ],
];
