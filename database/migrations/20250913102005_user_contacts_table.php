<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserContactsTable extends AbstractMigration
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
        $table = $this->table('user_contacts', ['engine' => 'InnoDB']);
        $table
            ->addTimestamps()
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('type', 'enum', ['values' => ['phone','email','address'], 'null' => false])
            ->addColumn('value', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('is_primary', 'boolean', ['default' => 0, 'null' => false])

            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION', 'constraint' => 'fk_uc_user'])
            ->addIndex(['user_id'], ['unique' => false])
            ->create();
    }
}
