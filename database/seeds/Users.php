<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Users extends AbstractSeed
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
        $table = $this->table('users');
        $table->insert([
            'login' => 'zaman',
            'name' => 'Лазарев Евгений',
            'password_hash' => password_hash('12345678', PASSWORD_DEFAULT),
            'is_admin' => false,
            'gender' => 'm',
            'birth_date' => '1990-02-21',
        ])->save();
    }
}
