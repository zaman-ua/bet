<?php

namespace App\Http;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Repository\BetRepository;
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

        if(Auth::isLoggedIn()) {
            $userId = Auth::getUserId();
            if($userId) {
                $amounts = (new UserRepository()->fetchAmountsById($userId));
                $bets = (new BetRepository())->fetchBetsByUserId($userId);
            }
        }

        $currencies = (new CurrencyRepository()->getAssoc());
        $matches = require APP_ROOT . '/config/matches.php';

        // такой же код в админке
        // доделываю на скорую руку, по хорошему вынести в сервис/репозиторий куда просто передать нассив ставок и матчей
        $bets = (new BetRepository())->processMatches($bets ?? [], $matches);

        return $this->render('home/index.html.twig', [
            'matches' => $matches,
            'amounts' => $amounts ?? [],
            'currencies' => $currencies ?? [],
            'bets' => $bets ?? [],
        ]);
    }
}