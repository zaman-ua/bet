<?php

namespace App\Http;

use App\Core\Http\Response;

class HomeController extends Controller
{
    public function __invoke() : Response
    {
        return $this->render('home/index.html.twig', [
            'a_variable' => 'a_variable aaa',
            'navigation' => [
                ['href' => '/', 'caption' => 'Home'],
                ['href' => '/about', 'caption' => 'about'],
            ]
        ]);
    }

    public function show() : Response
    {
//        // переменная из ссылки по роуту
//        $var = $this->request->getAttribute('id');
//
//        // переменная из квери параметров
//        $var = $this->request->query['aaa'];
//
//        return $this->render('home/index.html.twig', [
//            'a_variable' => $var,
//            'navigation' => [
//                ['href' => '/', 'caption' => 'Home'],
//                ['href' => '/about', 'caption' => 'about'],
//            ]
//        ]);
    }
}