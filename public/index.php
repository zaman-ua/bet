<?php

declare(strict_types=1);

// инициализация автолодера и базовых зависимостей
$app = require __DIR__ . '/../app/bootstrap.php';

// создаем экземпляр нашего приложения и запускаем обработку реквеста
$app->handle(App\Core\Http\Request::fromGlobals());
