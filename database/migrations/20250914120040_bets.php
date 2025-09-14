<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Bets extends AbstractMigration
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
        $table = $this->table('bets', ['engine' => 'InnoDB']);
        $table
            ->addTimestamps()
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('currency_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('match_id', 'integer', ['null' => false, 'signed' => false])

            ->addColumn('outcome', 'enum', ['values' => ['win','draw','loss'], 'null' => false]) // исход
            ->addColumn('stake','biginteger', ['default' => 0, 'null' => false]) // ставка
            ->addColumn('coefficient', 'integer', ['null' => false]) // коэффициент

            ->addColumn('status', 'enum', ['values' => ['placed','won','lost'], 'null' => false, 'default' => 'placed'])
            ->addColumn('payout','biginteger', ['default' => 0, 'null' => false]) // выплата


            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION', 'constraint' => 'fk_ual_user'])
            ->addForeignKey('currency_id', 'currencies', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION', 'constraint' => 'fk_ual_currency'])

            ->addIndex(['user_id', 'currency_id'], ['unique' => false, 'name' => 'idx_us_cur'])
            ->create();
    }
}
