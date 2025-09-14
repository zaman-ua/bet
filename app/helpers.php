<?php

// для удобства работы с .env файлом
// так же есть хелпер с дефолтным значением
$env = APP_ROOT . '/.env';
if (is_file($env)) {
    foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        // выше пропускаем пустые строки, а здесь строки комментариев начинающиеся символом #
        if ($line[0] === '#') continue;

        // разделяем ключ значение по первому знаку равно
        [$k,$v] = array_map('trim', explode('=', $line, 2));

        // подрезаем кавычки, что бы получить чистые строки
        $v = trim($v, "\"'");

        // записываем в глобальный массив $_ENV
        $_ENV[$k] = $v;

        // в окружение думаю записывать не стоит в данном проекте
        //putenv("$k=$v");
    }
}

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

function csrf_token(): string {
    // ему бы неплохо задать время жизни минут 5-10
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function assets(string $path): string
{
    // пока что заглушка
    return '/' . ltrim($path, '/');
}