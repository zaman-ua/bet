<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Currency extends AbstractMigration
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
        $table = $this->table('currencies', ['engine' => 'InnoDB']);
        $table
            ->addTimestamps()
            ->addColumn('code', 'string', ['limit' => 3, 'null' => false])
            ->addColumn('symbol', 'string', ['limit' => 6, 'null' => false])
            ->addColumn('is_base_currency', 'boolean', ['default' => 0, 'null' => false])
            ->addColumn('convert_value', 'integer', ['null' => false, 'default' => 1, 'signed' => false])

            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['is_base_currency'], ['unique' => false])
            ->create();
    }
}
