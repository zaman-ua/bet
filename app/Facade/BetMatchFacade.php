<?php

namespace App\Facade;

use App\DTO\BetViewDTO;
use App\Interface\BetReaderRepositoryInterface;
use App\Interface\MatchConfigProviderInterface;
use App\Services\MatchPresentationService;

final class BetMatchFacade
{
    public function __construct(
        private readonly BetReaderRepositoryInterface  $betReaderRepository,
        private readonly MatchPresentationService      $matchPresentationService,
        private readonly MatchConfigProviderInterface  $matchConfigProvider,
    ) {}

    public function getMatches(): array
    {
        return $this->matchConfigProvider->getMatches();
    }

    public function getBetsWithMatchesForUser(int $userId): array
    {
        $bets = $this->betReaderRepository->fetchBetsByUserId($userId);
        $matches = $this->matchConfigProvider->getMatches();

        return $this->matchPresentationService->attachMatches($bets, $matches);
    }

    public function getBetById(int $betId) : ?BetViewDTO
    {
        return $this->betReaderRepository->getById($betId);
    }

    public function getAllBetsWithMatches(): array
    {
        $bets = $this->betReaderRepository->fetchAll();

        return $this->matchPresentationService->attachMatches(
            $bets,
            $this->getMatches(),
        );
    }
}