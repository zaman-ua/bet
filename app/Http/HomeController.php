<?php

namespace App\Http;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Repository\UserRepository;

final class HomeController extends Controller
{
    public function __invoke() : Response
    {
        // такого не должно быть, но в жизни всякое бывает
        if(Auth::isAdmin()) {
            return $this->redirect('/admin/bets');
        }

        $amounts = (new UserRepository()->fetchAmountsById(Auth::getUserId()));

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
            'id'   => 2,
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
            'amounts' => $amounts
        ]);
    }
}