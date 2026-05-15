<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateChatsTable extends AbstractMigration
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
        $table = $this->table('chats');
        $table->addColumn('group_id',   'integer',  ['signed' => false, 'null' => false])
            ->addColumn('sender_id',    'integer',  ['signed' => false, 'null' => false])
            ->addColumn('content',      'text',     ['null' => false])
            ->addColumn('image_url',    'string',   ['limit' => 255, 'null' => true])
            ->addColumn('created_at',   'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            
            // 外部キー制約の設定
            ->addForeignKey('group_id', 'groups', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addForeignKey('sender_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->create();
    }
}
