<?php

namespace App\Traits;

use App\Core\Interface\ResponseInterface;
use App\Exception\TwigRenderException;
use Throwable;

trait WithTwigRenderTrait
{
    use WithTwigFetchTrait;

// такие контроллеры у нас используются для выдачи html
    // по этому здесь и подключаем шаблонизатор
    // сразу же формируем респонс с правильным кодом ответа
    // в контроллере в конце нужно будет только вызвать render() с указанием шаблона и передать переменные шаблона
    public function render(string $template, array $vars = [], int $code = 200) : ResponseInterface
    {
        try {
            // подмешиваем ошибки валидации и старые данные что бы можно было отобразить в форме
            $body = $this->fetch($template, array_merge($vars ?? [], [
                'old' => $this->oldData,
                'errors' => $this->errors,
            ]));

        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }

        return $this->html($body, $code);
    }
}