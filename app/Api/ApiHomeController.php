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

        ]);
    }
}