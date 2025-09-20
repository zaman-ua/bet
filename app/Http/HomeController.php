<?php

namespace App\Http;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Facade\BetMatchFacade;
use App\Facade\UserCurrencyFacade;
use App\Traits\WithTwigRenderTrait;

final class HomeController extends Controller
{
    use WithTwigRenderTrait;

    public function __construct(
        RequestInterface                               $request,
        ResponseInterface                              $response,
        AuthServiceInterface                           $authService,
        private readonly BetMatchFacade                $betMatchFacade,
        private readonly UserCurrencyFacade            $userCurrencyFacade,
    ) {
        parent::__construct($request, $response, $authService);
    }

    public function __invoke() : ResponseInterface
    {
        // такого не должно быть, но в жизни всякое бывает
        if($this->authService->isAdmin()) {
            return $this->redirect('/admin/bets');
        }

        if($this->authService->isLoggedIn()) {
            $userId = $this->authService->getUserId();
            if($userId) {
                $amounts = $this->userCurrencyFacade->fetchUserAmountsById($userId);
                $bets = $this->betMatchFacade->getBetsWithMatchesForUser($userId);
            }
        }

        $currencies = $this->userCurrencyFacade->getCurrencyAssoc();
        $matches = $this->betMatchFacade->getMatches();

        return $this->render('home/index.html.twig', [
            'matches' => $matches,
            'amounts' => $amounts ?? [],
            'currencies' => $currencies ?? [],
            'bets' => $bets ?? [],
        ]);
    }
}