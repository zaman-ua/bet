<?php

use App\Core\Db\PdoProvider;

return [
    'provider' => env('DB_PROVIDER', 'PdoProvider'),

    'driver'   => env('DB_DRIVER', 'mysql'),
    'host'     => env('DB_HOST', '127.0.0.1'),
    'port'     => (int) env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'bet'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
    'collation'=> env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'timezone' => env('APP_TZ', 'UTC'),
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
