<?php

namespace App\Facade;

use App\Interface\CurrencyRepositoryInterface;
use App\Interface\UserReaderRepositoryInterface;
use App\Services\AmountPresentationService;

final class UserCurrencyFacade
{
    public function __construct(
        private readonly CurrencyRepositoryInterface   $currencyRepository,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        private readonly AmountPresentationService     $amountPresentationService,
    ) {}

    public function getCurrencyAssoc() : ?array
    {
        return $this->currencyRepository->getAssoc();
    }

    public function fetchAllUsers() : ?array
    {
        $users = $this->userReaderRepository->fetchAll();
        return $this->amountPresentationService->fetchAmounts($users);
    }

    public function fetchUserAmountsById(int $userId) : array
    {
        $amount = $this->userReaderRepository->fetchAmountsById($userId);
        return $this->amountPresentationService->fetchAmount($amount);
    }
}