<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Admin extends AbstractSeed
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
            'login' => 'admin',
            'name' => 'Admin',
            'password_hash' => password_hash('1234qwer', PASSWORD_DEFAULT),
            'is_admin' => true,
        ])->save();
    }
}
