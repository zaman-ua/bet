<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserAmountLogs extends AbstractMigration
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
        $table = $this->table('user_amount_logs', ['engine' => 'InnoDB']);
        $table
            ->addTimestamps()
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('currency_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('bet_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('type', 'enum', ['values' => ['admin_adjust','bet_place','bet_win','deposit','withdraw','refund'], 'null' => false])
            ->addColumn('amount','biginteger', ['default' => 0, 'null' => false])
            ->addColumn('comment', 'string', ['limit' => 255, 'null' => true])

            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE', 'update' => 'RESTRICT', 'constraint' => 'fk_user_amount_logs_user'
            ])
            ->addForeignKey('currency_id', 'currencies', 'id', [
                'delete' => 'CASCADE', 'update' => 'RESTRICT', 'constraint' => 'fk_user_amount_logs_currency'
            ])
            ->addForeignKey('bet_id', 'bets', 'id', [
                'delete' => 'CASCADE', 'update' => 'RESTRICT', 'constraint' => 'fk_user_amount_logs_bet'
            ])

            ->addIndex(['user_id', 'currency_id'], ['unique' => false, 'name' => 'idx_us_cur'])
            ->create();
    }
}
