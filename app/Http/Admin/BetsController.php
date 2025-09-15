<?php

namespace App\Http\Admin;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Http\Controller;
use App\Repository\BetRepository;

final class BetsController extends Controller
{
    public function __invoke() : Response
    {
        // если пользователь зашел куда его не просили
        if(!Auth::isAdmin()) {
            return $this->redirect('/');
        }

        $bets = (new BetRepository())->fetchAll();

        return $this->render('admin/bets.html.twig', [
            'bets' => $bets,
        ]);
    }
}