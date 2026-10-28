<?php
namespace local_helizllama\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Límite de peticiones por usuario.
 *
 * Sin esto, un usuario autenticado puede disparar peticiones en bucle. Hoy solo
 * gastaría CPU, pero cuando detrás haya un modelo de lenguaje cada petición
 * cuesta segundos de GPU, así que el límite tiene que existir antes de conectar
 * la IA, no después.
 */
class rate_limiter {

    /** Peticiones permitidas dentro de la ventana. */
    const MAX_PETICIONES = 20;

    /** Tamaño de la ventana en segundos. */
    const VENTANA = 60;

    /**
     * Registra una petición y dice si el usuario se pasó del límite.
     *
     * @param int $userid
     * @return bool true si puede continuar, false si excedió el límite.
     */
    public static function permitir(int $userid): bool {
        $cache = \cache::make_from_params(
            \cache_store::MODE_APPLICATION,
            'local_helizllama',
            'ratelimit'
        );

        $key = 'u' . $userid;
        $ahora = time();
        $registro = $cache->get($key);

        if (!is_array($registro) || ($ahora - ($registro['inicio'] ?? 0)) >= self::VENTANA) {
            $registro = ['inicio' => $ahora, 'conteo' => 0];
        }

        $registro['conteo']++;
        $cache->set($key, $registro);

        return $registro['conteo'] <= self::MAX_PETICIONES;
    }
}
