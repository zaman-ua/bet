<?php

namespace App\Repository;

use App\Core\Db\Db;

final class CurrencyRepository
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