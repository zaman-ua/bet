<?php

namespace App\Provider;

use App\Interface\MatchConfigProviderInterface;

final class MatchConfigProvider implements MatchConfigProviderInterface
{
    public function __construct(
        private readonly string $configPath = APP_ROOT . '/config'
    ) {}

    public function getBetConfig(): array
    {
        $config = require $this->configPath . '/bets.php';

        return $config;
    }

    public function getMatches(): array
    {
        $config = require $this->configPath . '/matches.php';

        return $config;
    }
}