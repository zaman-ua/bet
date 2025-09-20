<?php

namespace App\Core;

use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Exception\ErrorHandler;
use App\Http\Controller;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class ControllerInvoker
{
    public function __construct(
        private Container $container,
        private ErrorHandler $errorHandler,
    ) {
    }

    public function invoke(mixed $handler, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // оборачиваем вызов контроллеров проверкой на исключения
        try {
            // вызов контроллера
            if (is_string($handler)) {
                // для инвокабл контроллера
                return $this->resolveController($handler, $request, $response)();

            } elseif (is_array($handler)) {
                // для конкретного метода контроллера
                [$class, $method] = [$handler[0], $handler[1]];
                $controller = $this->resolveController($class, $request, $response);

                return $controller->{$method}();
            }

            throw new RuntimeException('Invalid handler provided for controller invocation');
        } catch (\Throwable $e) {
            // кастомный обработчик исключений,
            // что бы красиво вывести для api и браузерных запросов
            return $this->errorHandler->render($e, $request, $response);
        }
    }

    private function resolveController(string $class, RequestInterface $request, ResponseInterface $response): Controller
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            /** @var Controller $controller */
            $controller = $reflection->newInstance($request, $response);

            return $controller;
        }

        $parameters = $constructor->getParameters();

        if (count($parameters) < 2) {
            throw new RuntimeException("Controller '{$class}' must accept Request and Response as the first arguments");
        }

        $arguments = [$request, $response];

        foreach (array_slice($parameters, 2) as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->container->get($type->getName());

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException("Cannot resolve dependency '{$parameter->getName()}' for controller '{$class}'");
        }

        /** @var Controller $controller */
        $controller = $reflection->newInstanceArgs($arguments);

        return $controller;
    }
}