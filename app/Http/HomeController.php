<?php

namespace App\Http;

class HomeController extends Controller
{
    public function __invoke()
    {
        return $this->render('index.html.twig', [
            'a_variable' => 'a_variable aaa',
            'navigation' => [
                ['href' => '/', 'caption' => 'Home'],
                ['href' => '/about', 'caption' => 'about'],
            ]
        ]);
    }

    public function show()
    {
        // переменная из ссылки по роуту
        $var = $this->request->getAttribute('id');

        // переменная из квери параметров
        $var = $this->request->query['aaa'];

        return $this->render('index.html.twig', [
            'a_variable' => $var,
            'navigation' => [
                ['href' => '/', 'caption' => 'Home'],
                ['href' => '/about', 'caption' => 'about'],
            ]
        ]);
    }
}