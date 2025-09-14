<?php

namespace App\Http;

use App\Core\Http\Response;

class HomeController extends Controller
{
    public function __invoke() : Response
    {
        $config = require APP_ROOT . '/config/bets.php';


        $out[] = [
            'id'   => 1,
            'win' => 'Команда 1',
            'loss' => 'Команда 2',
            'odds' => [
                'win' => 2.50,
                'draw' => 3.05,
                'loss' => 3.15
            ],
        ];

        $out[] = [
            'id'   => 1,
            'win' => 'Команда 3',
            'loss' => 'Команда 4',
            'odds' => [
                'win' => 1.45,
                'draw' => 3.45,
                'loss' => 5.87
            ],
        ];


        return $this->render('home/index.html.twig', [
            'matches' => $out,
        ]);
    }
}