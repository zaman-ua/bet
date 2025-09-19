<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Interface\RequestInterface;

// Мини-роутер с плейсхолдерами {id} и {id:\d+}
final class Router
{
    /** @var array<string, array<int, array{regex:string, vars:array, handler:mixed}>> */
    private array $routes = [];

    // добавление маршрутов
    public function add(string $method, string $pattern, mixed $handler): void
    {
        $vars = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}#',
            function ($m) use (&$vars) {
                $vars[] = $m[1];
                $r = $m[2] ?? '[^/]+';
                return '(?P<' . $m[1] . '>' . $r . ')';
            },
            $pattern
        );
        $regex = '#^' . $regex . '$#';

        $this->routes[$method][] = [
            'regex' => $regex,
            'vars' => $vars,
            'handler' => $handler,
        ];
    }

    // проверка соответствия маршруту
    public function match(RequestInterface $request): ?array
    {
        $list = $this->routes[$request->getMethod()] ?? [];

        foreach ($list as $route) {
            if (preg_match($route['regex'], $request->getPath(), $match)) {
                $params = [];
                foreach ($route['vars'] as $value) {
                    $params[$value] = $match[$value];
                }
                return [$route['handler'], $params];
            }
        }
        return null;
    }

    // создание фабрики и загрузка маршрутов из файла
    public static function fromFile(string $file): self
    {
        $router = new self();
        $definer = require $file;
        $definer($router);
        return $router;
    }
}
