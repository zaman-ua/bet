<?php

namespace App\FragmentsService;

use App\Facade\BetMatchFacade;
use App\Interface\UserReaderRepositoryInterface;
use App\Services\AmountPresentationService;
use App\Traits\WithTwigFetchTrait;

final class UserAmountFragmentsService
{
    use WithTwigFetchTrait;

    public function __construct(
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        private readonly BetMatchFacade                $betMatchFacade,
        private readonly AmountPresentationService     $amountPresentationService,
    ) {}

    public function buildAmountForUser(int $userId): string
    {
        $amount = $this->userReaderRepository->fetchAmountsById($userId);
        $amountArray = $this->amountPresentationService->fetchAmount($amount);

        return $this->fetch('shared/user_amounts.html.twig', [
            'amounts_array' => $amountArray,
        ]);
    }

    public function buildBetTableForUser(int $userId): string
    {
        $bets = $this->betMatchFacade->getBetsWithMatchesForUser($userId);

        return $this->fetch('shared/user_bets.html.twig', [
            'bets' => $bets,
        ]);
    }
}