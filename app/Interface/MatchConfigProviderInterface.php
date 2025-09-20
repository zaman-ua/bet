<?php

namespace App\Interface;

interface MatchConfigProviderInterface
{
    public function getBetConfig(): array;
    public function getMatches(): array;
}