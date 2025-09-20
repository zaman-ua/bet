<?php

namespace App\Http;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Interface\BetReaderRepositoryInterface;
use App\Interface\CurrencyRepositoryInterface;
use App\Interface\MatchConfigProviderInterface;
use App\Interface\UserReaderRepositoryInterface;
use App\Services\MatchPresentationService;
use App\Traits\WithTwigTrait;

final class HomeController extends Controller
{
    use WithTwigTrait;

    public function __construct(
        RequestInterface                               $request,
        ResponseInterface                              $response,
        private readonly CurrencyRepositoryInterface   $currencyRepository,
        private readonly BetReaderRepositoryInterface  $betReaderRepository,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        AuthServiceInterface                           $authService,
        private readonly MatchConfigProviderInterface  $matchConfigProvider,
        private readonly MatchPresentationService      $matchPresentationService,
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
                $bets = $this->betReaderRepository->fetchBetsByUserId($userId);
            }
        }

        $currencies = $this->currencyRepository->getAssoc();
        $matches = $this->matchConfigProvider->getMatches();

        $bets = $this->matchPresentationService->attachMatches($bets ?? [], $matches);

        return $this->render('home/index.html.twig', [
            'matches' => $matches,
            'amounts' => $amounts ?? [],
            'currencies' => $currencies ?? [],
            'bets' => $bets ?? [],
        ]);
    }
}