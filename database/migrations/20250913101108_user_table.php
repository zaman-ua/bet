<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('users', ['engine' => 'InnoDB']);
        $table
            ->addTimestamps()
            ->addColumn('login', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('gender', 'enum', ['values' => ['m', 'f', 'o'], 'default' => 'o', 'null' => false])
            ->addColumn('birth_date', 'date', ['null' => false])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactive'], 'default' => 'active', 'null' => false])

            ->addIndex(['login'], ['unique' => true])
            ->addIndex(['gender'], ['unique' => false])
            ->addIndex(['status'], ['unique' => false])
            ->create();
    }
}
