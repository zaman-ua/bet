<?php

// хелпер для работы с .env с указанием дефолтных значений
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        if (!array_key_exists($key, $_ENV)) {
            return $default;
        }
        $val = $_ENV[$key];

        $lower = is_string($val) ? strtolower($val) : $val;
        return match ($lower) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            default            => $val,
        };
    }
}
