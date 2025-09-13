<?php

// штука нужная для миграций и сидов
// нет смысла в 2025 году писать sql для создания и наполнения базы вручную
// практические знания sql все также будут отражаться в использовании функционала phinx
// даже для маленьких проектов имеет смысл, так как все изменения структуры базы можно отследить системой контроля версий кода (git)

require __DIR__ . '/app/bootstrap.php';

$db = require __DIR__ . '/config/database.php';

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => $db['driver'],
            'host' => $db['host'],
            'name' => $db['database'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => $db['port'],
            'charset' => $db['charset'],
        ],
    ],
    'version_order' => 'creation'
];
