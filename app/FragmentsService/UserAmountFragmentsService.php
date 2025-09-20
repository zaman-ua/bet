<?php

namespace App\FragmentsService;

use App\Facade\BetMatchFacade;
use App\Interface\UserReaderRepositoryInterface;
use App\Traits\WithTwigFetchTrait;

final class UserAmountFragmentsService
{
    use WithTwigFetchTrait;

    public function __construct(
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        private readonly BetMatchFacade                $betMatchFacade
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
        $bets = $this->betMatchFacade->getBetsWithMatchesForUser($userId);

        return $this->fetch('shared/user_bets.html.twig', [
            'bets' => $bets,
        ]);
    }
}