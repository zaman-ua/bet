<?php

namespace App\Interface;

use App\DTO\BetCreateDTO;
use App\DTO\BetViewDTO;

interface BetReaderRepositoryInterface
{
    public function fetchAll() : array;

    public function fetchBetsByUserId(int $userId) : array;

    public function getById(int $betId) : ?BetViewDTO;

}