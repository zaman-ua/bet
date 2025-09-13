<?php

namespace App\Api;

use App\Core\Db\Db;

class ApiHomeController extends ApiController
{
    public function __invoke()
    {
        return $this->json([
            'var1' => 'bbbb',
            'var2' => $this->request->getAttribute('id'),

            'var3' => Db::getOne('SELECT COUNT(*) FROM users'),
            'var4' => Db::getOne('SELECT COUNT(*) FROM users WHERE id = :id', ['id' => 1]),
            'var5' => Db::getRow('SELECT * FROM users'),
            'var6' => Db::getRow('SELECT * FROM users WHERE id = :id', ['id' => 3]),
            'var7' => Db::getAll('SELECT * FROM users'),
            'var8' => Db::getAssoc('SELECT id, name, login FROM users'),
        ]);
    }
}