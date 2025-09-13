<?php

namespace App\Http;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Exception\TwigRenderException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    // применим фишки php8 с автоматическим созданием атрибута
    // через указание в параметрах конструктора
    public function __construct(public Request $request, public Response $response) {}

    // такие контроллеры у нас используются для выдачи html
    // по этому здесь и подключаем шаблонизатор
    // сразу же формируем респонс с правильным кодом ответа
    // в контроллере в конце нужно будет только вызвать render() с указанием шаблона и передать переменные шаблона
    public function render(string $template, array $vars = []) : Response
    {
        try {
            $loader = new FilesystemLoader(APP_ROOT . env('TWIG_TEMPLATE', '/templates'));
            $twig = new Environment($loader, [
                'cache' => APP_ROOT . env('TWIG_TEMPLATE_CACHE', '/templates/templates_c')
            ]);

            $template = $twig->load($template);
            $body = $template->render($vars);

        } catch (\Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }

        // фишка php8 Constructor Property Promotion
        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withStatus(200)
            ->write($body);
    }
}