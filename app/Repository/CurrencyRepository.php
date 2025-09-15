<?php

namespace App\Repository;

use App\Core\Db\Db;

final class CurrencyRepository
{
    public function getAssoc() {
        return Db::getAssoc("SELECT id, symbol FROM currencies");
    }
}