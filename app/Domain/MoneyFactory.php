<?php

namespace App\Domain;

use App\Interface\CurrencyRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;

final class MoneyFactory
{
    public function __construct(
        private readonly CurrencyRepositoryInterface $currencyRepository,
    ) {
    }

    public function fromHuman(string $amount, int $currencyId): Money
    {
        $amount = trim(str_replace(' ', '', $amount));
        $amount = str_replace(',', '.', $amount);

        if ($amount === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException("Invalid money: '{$amount}'");
        }

        $amountInt = (int) ((float) $amount * 100);
        $symbol = $this->currencyRepository->getSymbolById($currencyId);

        if ($symbol === null) {
            throw new RuntimeException('Invalid currency code or id');
        }

        return new Money($amountInt, $currencyId, $symbol);
    }

    public function fromRaw(int $amount, ?int $currencyId = null, ?string $currencyCode = null): Money
    {
        if ($currencyId !== null) {
            $symbol = $this->currencyRepository->getSymbolById($currencyId);

            if ($symbol === null) {
                throw new RuntimeException('Invalid currency code or id');
            }

            return new Money($amount, $currencyId, $symbol);
        }

        if ($currencyCode !== null) {
            $currencyId = $this->currencyRepository->getIdByCode($currencyCode);

            if ($currencyId === null) {
                throw new RuntimeException('Invalid currency code or id');
            }

            $symbol = $this->currencyRepository->getSymbolById($currencyId);

            if ($symbol === null) {
                throw new RuntimeException('Invalid currency code or id');
            }

            return new Money($amount, $currencyId, $symbol);
        }

        throw new RuntimeException('Invalid currency code or id');
    }
}