<?php

namespace App\Http;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Facade\BetMatchFacade;
use App\Interface\CurrencyRepositoryInterface;
use App\Interface\UserReaderRepositoryInterface;
use App\Traits\WithTwigRenderTrait;

final class HomeController extends Controller
{
    use WithTwigRenderTrait;

    public function __construct(
        RequestInterface                               $request,
        ResponseInterface                              $response,
        private readonly CurrencyRepositoryInterface   $currencyRepository,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        AuthServiceInterface                           $authService,
        private readonly BetMatchFacade                $betMatchFacade
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
                $amounts = $this->userReaderRepository->fetchAmountsById($userId);
                $bets = $this->betMatchFacade->getBetsWithMatchesForUser($userId);
            }
        }

        $currencies = $this->currencyRepository->getAssoc();
        $matches = $this->betMatchFacade->getMatches();

        return $this->render('home/index.html.twig', [
            'matches' => $matches,
            'amounts' => $amounts ?? [],
            'currencies' => $currencies ?? [],
            'bets' => $bets ?? [],
        ]);
    }
}