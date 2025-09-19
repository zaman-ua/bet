<?php

namespace App\Interface;

interface CurrencyRepositoryInterface
{
    public function getAssoc(): ?array;

    public function getSymbolById(int $currencyId): ?string;

    public function getIdByCode(string $currencyCode): ?int;
}