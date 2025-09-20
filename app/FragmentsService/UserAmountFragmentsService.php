<?php

namespace App\FragmentsService;

use App\Interface\BetReaderRepositoryInterface;
use App\Interface\MatchConfigProviderInterface;
use App\Interface\UserReaderRepositoryInterface;
use App\Services\MatchPresentationService;
use App\Traits\WithTwigTrait;

final class UserAmountFragmentsService
{
    use WithTwigTrait;

    public function __construct(
        private readonly BetReaderRepositoryInterface  $betReaderRepository,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        private readonly MatchPresentationService      $matchPresentationService,
        private readonly MatchConfigProviderInterface  $matchConfigProvider,
    ) {}

    public function buildAmountForUser(int $userId): string
    {
        $amountArray = $this->userReaderRepository->fetchAmountsById($userId);
        return $this->fetch('shared/user_amounts.html.twig', [
            'amounts_array' => $amountArray,
        ]);
    }

    public function buildBetTableForUser(int $userId): string
    {
        $bets = $this->betReaderRepository->fetchBetsByUserId($userId);
        $bets = $this->matchPresentationService->attachMatches($bets, $this->matchConfigProvider->getMatches());

        return $this->fetch('shared/user_bets.html.twig', [
            'bets' => $bets,
        ]);
    }
}