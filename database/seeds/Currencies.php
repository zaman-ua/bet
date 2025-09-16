<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Currencies extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $table = $this->table('currencies');
        $table
            ->insert([
                'code' => 'USD',
                'symbol' => 'USD',
            ])
            ->insert([
                'code' => 'EUR',
                'symbol' => 'EUR',
            ])
            ->insert([
                'code' => 'RUB',
                'symbol' => 'RUB',
            ])
            ->save();
    }
}
