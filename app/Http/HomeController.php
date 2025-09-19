<?php

namespace App\Http;

use App\Core\Auth;
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Interface\BetRepositoryInterface;
use App\Interface\CurrencyRepositoryInterface;
use App\Interface\UserRepositoryInterface;
use App\Traits\WithTwigTrait;

final class HomeController extends Controller
{
    use WithTwigTrait;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        private readonly CurrencyRepositoryInterface $currencyRepository,
        private readonly BetRepositoryInterface $betRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {
        parent::__construct($request, $response);
    }

    public function __invoke() : ResponseInterface
    {
        // такого не должно быть, но в жизни всякое бывает
        if(Auth::isAdmin()) {
            return $this->redirect('/admin/bets');
        }

        if(Auth::isLoggedIn()) {
            $userId = Auth::getUserId();
            if($userId) {
                $amounts = $this->userRepository->fetchAmountsById($userId);
                $bets = $this->betRepository->fetchBetsByUserId($userId);
            }
        }

        $currencies = $this->currencyRepository->getAssoc();
        $matches = require APP_ROOT . '/config/matches.php';

        $bets = $this->betRepository->processMatches($bets ?? [], $matches);

        return $this->render('home/index.html.twig', [
            'matches' => $matches,
            'amounts' => $amounts ?? [],
            'currencies' => $currencies ?? [],
            'bets' => $bets ?? [],
        ]);
    }
}