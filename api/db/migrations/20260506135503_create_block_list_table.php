<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBlockListTable extends AbstractMigration
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
        $table = $this->table('block_list');
        $table->addColumn('user_id',        'integer',  ['null' => false, 'signed' => false])
            ->addColumn('blocked_user_id',  'integer',  ['null' => false, 'signed' => false])
            ->addColumn('created_at',       'datetime', ['default' => 'CURRENT_TIMESTAMP'])

            // UNIQUE制約
            ->addIndex(['user_id', 'blocked_user_id'], ['unique' => true])

            // 外部キー制約
            ->addForeignKey('user_id',          'users', 'id', ['delete' => 'cascade'])
            ->addForeignKey('blocked_user_id',  'users', 'id', ['delete' => 'cascade'])
            ->create();
    }
}
