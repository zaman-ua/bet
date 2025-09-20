<?php

namespace App\Services;

use App\DTO\BetViewDTO;

final class MatchPresentationService
{
    /**
     * @param BetViewDTO[] $bets
     * @param array<int, array{win?: string, loss?: string}> $matches
     * @return BetViewDTO[]
     */
    public function attachMatches(array $bets, array $matches): array
    {
        foreach ($bets as $index => $bet) {
            $bets[$index] = $this->attachMatch($bet, $matches);
        }

        return $bets;
    }

    /**
     * @param array<int, array{win?: string, loss?: string}> $matches
     */
    public function attachMatch(BetViewDTO $bet, array $matches): BetViewDTO
    {
        if (!isset($matches[$bet->match_id])) {
            return $bet;
        }

        $match = $matches[$bet->match_id];
        $win = $match['win'] ?? '';
        $loss = $match['loss'] ?? '';
        $title = trim($win . ' - ' . $loss, ' -');

        if ($title === '') {
            return $bet;
        }

        return $bet->withMatch($title);
    }
}