<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserAmount extends AbstractMigration
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
        $table = $this->table('user_amounts', ['engine' => 'InnoDB']);
        $table
            ->addTimestamps()
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('currency_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('value', 'integer', ['default' => 0, 'null' => false])

            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION', 'constraint' => 'fk_ua_user'])
            ->addForeignKey('currency_id', 'currencies', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION', 'constraint' => 'fk_ua_currency'])
            ->addIndex(['user_id', 'currency_id'], ['unique' => true, 'name' => 'idx_us_cur'])
            ->create();
    }
}
