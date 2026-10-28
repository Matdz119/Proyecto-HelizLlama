<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'block_helizllama';
$plugin->version   = 2026102703;
$plugin->requires  = 2024100100; // Moodle 4.5+
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1.0 (placeholder sin IA)';
$plugin->dependencies = [
    'local_helizllama' => 2026102703,
];
