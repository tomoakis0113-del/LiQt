<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBlogsTable extends AbstractMigration
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
        $table = $this->table('blogs');
        $table->addColumn('author_id',  'integer',  ['signed' => false, 'null' => false])
            ->addColumn('group_id',     'integer',  ['signed' => false, 'null' => true])
            ->addColumn('title',        'string',   ['limit' => 255, 'null' => false])
            ->addColumn('content',      'text',     ['null' => false]) // Markdown形式
            ->addColumn('tags',         'text',     ['null' => true])  // カンマ区切り
            ->addColumn('visibility',   'enum', [
                'values'  => ['public', 'private', 'group'],
                'default' => 'public'
            ])
            ->addColumn('created_at',   'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',   'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'update'  => 'CURRENT_TIMESTAMP' // これが ON UPDATE に相当
            ])
            
            // 外部キー制約
            ->addForeignKey('author_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addForeignKey('group_id', 'groups', 'id', [
                'delete' => 'SET_NULL', // SQLの指定通り、グループ削除時はNULLにする
                'update' => 'NO_ACTION'
            ])
            ->create();
    }
}
