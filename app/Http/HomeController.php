<?php

namespace App\Http;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Repository\CurrencyRepository;
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
        $currencies = (new CurrencyRepository()->getAssoc());

        $matches = require APP_ROOT . '/config/matches.php';

        return $this->render('home/index.html.twig', [
            'matches' => $matches,
            'amounts' => $amounts,
            'currencies' => $currencies,
        ]);
    }
}