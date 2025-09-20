<?php

namespace App\Facade;

use App\Interface\CurrencyRepositoryInterface;
use App\Interface\UserReaderRepositoryInterface;

final class UserCurrencyFacade
{
    public function __construct(
        private readonly CurrencyRepositoryInterface   $currencyRepository,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
    ) {}

    public function getCurrencyAssoc() : ?array
    {
        return $this->currencyRepository->getAssoc();
    }

    public function fetchAllUsers() : ?array
    {
        return $this->userReaderRepository->fetchAll();
    }

    public function fetchUserAmountsById(int $userId) : array
    {
        return $this->userReaderRepository->fetchAmountsById($userId);
    }
}