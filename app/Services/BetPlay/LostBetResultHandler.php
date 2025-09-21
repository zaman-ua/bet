<?php

namespace App\Services\BetPlay;

use App\Interface\BetWriterRepositoryInterface;

final class LostBetResultHandler implements BetResultHandlerInterface
{
    public function __construct(
        private readonly BetWriterRepositoryInterface $betWriterRepository,
    ) {}

    public function handle(int $betId, array $bet): int
    {
        $this->betWriterRepository->markLost($betId);
        return 0;
    }
}