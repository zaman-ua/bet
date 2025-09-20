<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\Interface\CurrencyRepositoryInterface;

final class CurrencyRepository implements CurrencyRepositoryInterface
{
    public function __construct(
        private readonly DbInterface $db,
    ) {
    }
    
    public function getAssoc() : ?array
    {
        return $this->db->getAssoc("SELECT id, symbol FROM currencies");
    }

    public function getSymbolById(int $currencyId): ?string
    {
        return $this->db->getOne("SELECT symbol FROM currencies WHERE id = :currencyId", ['currencyId' => $currencyId]);
    }

    public function getIdByCode(string $currencyCode): ?int
    {
        return $this->db->getOne("SELECT id FROM currencies WHERE code = :currencyCode", ['currencyCode' => $currencyCode]);
    }
}