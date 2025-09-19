<?php

namespace App\Repository;

use App\Core\Db\Db;
use App\Interface\CurrencyRepositoryInterface;

final class CurrencyRepository implements CurrencyRepositoryInterface
{
    public function getAssoc() : ?array
    {
        return Db::getAssoc("SELECT id, symbol FROM currencies");
    }

    public function getSymbolById(int $currencyId): ?string
    {
        return Db::getOne("SELECT symbol FROM currencies WHERE id = :currencyId", ['currencyId' => $currencyId]);
    }

    public function getIdByCode(string $currencyCode): ?int
    {
        return Db::getOne("SELECT id FROM currencies WHERE code = :currencyCode", ['currencyCode' => $currencyCode]);
    }
}