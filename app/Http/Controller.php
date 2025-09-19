<?php

namespace App\Http;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Exception\TwigRenderException;
use App\Validation\RequestValidator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Throwable;

abstract class Controller
{
    protected array $oldData = [];
    public array $errors = [];

    // применим фишки php8 с автоматическим созданием атрибута
    // через указание в параметрах конструктора
    public function __construct(public RequestInterface $request, public ResponseInterface $response) {
        $this->oldData = $this->request->post ?? [];
    }
    public function json(array $payload = [], int $code = 200) : ResponseInterface
    {
        // сделаем вывод json красивым в Chrome браузере
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($code)
            ->write($body);
    }

    public function validate(array $data, array $validation) : array
    {
        [$result, $errors] = RequestValidator::validate($data, $validation);

        $this->errors = $errors;
        return $result;
    }

    // такие контроллеры у нас используются для выдачи html
    // по этому здесь и подключаем шаблонизатор
    // сразу же формируем респонс с правильным кодом ответа
    // в контроллере в конце нужно будет только вызвать render() с указанием шаблона и передать переменные шаблона
    public function render(string $template, array $vars = [], int $code = 200) : ResponseInterface
    {
        try {
            // подмешиваем ошибки валидации и старые данные что бы можно было отобразить в форме
            $body = $this->fetch($template, $vars);

        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }

        // фишка php8 Constructor Property Promotion
        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
//            ->withHeader('X-Content-Type-Options', 'nosniff')
//            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
//            ->withHeader('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'")
            ->withStatus($code)
            ->write($body);
    }

    public function fetch(string $template, array $vars = []) : ?string
    {
        try {
            $twig = $this->initTwig();

            $template = $twig->load($template);

            // подмешиваем ошибки валидации и старые данные что бы можно было отобразить в форме
            return $template->render(array_merge($vars ?? [], [
                'old' => $this->oldData,
                'errors' => $this->errors,
            ]));

        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }
    }

    public function redirect(string $url, int $code = 301) : ResponseInterface
    {
        return $this->response
            ->withHeader('HTTP/1.1 301 Moved Permanently','')
            ->withHeader('Location', $url)
            ->withStatus($code)
            ->write('');
    }

    private function initTwig(): Environment
    {
        try {
            $loader = new FilesystemLoader(APP_ROOT . env('TWIG_TEMPLATE', '/templates'));
            $twig = new Environment($loader, [
                //'cache' => APP_ROOT . env('TWIG_TEMPLATE_CACHE', '/templates/templates_c')
                'autoescape' => 'html' // помогает от xss
            ]);

            $twig->addFunction(new TwigFunction('env', 'env'));
            $twig->addFunction(new TwigFunction('assets', 'assets'));
            $twig->addFunction(new TwigFunction('csrf_token', 'csrf_token'));
            $twig->addFunction(new TwigFunction('var_dump', 'var_dump'));
            $twig->addFunction(new TwigFunction('isLoggedIn', 'App\Core\Auth::isLoggedIn'));
            $twig->addFunction(new TwigFunction('getUser', 'App\Core\Auth::getUser'));
            $twig->addFunction(new TwigFunction('isAdmin', 'App\Core\Auth::isAdmin'));
            $twig->addFunction(new TwigFunction('getUserId', 'App\Core\Auth::getUserId'));

            return $twig;
        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }
    }
}