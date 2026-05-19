<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBlogCommentsTable extends AbstractMigration
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
        $table = $this->table('blog_comments');
        $table->addColumn('blog_id',    'integer',  ['signed' => false, 'null' => false])
            ->addColumn('user_id',      'integer',  ['signed' => false, 'null' => false])
            ->addColumn('content',      'text',     ['null' => false])
            ->addColumn('created_at',   'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            
            // 外部キー制約
            ->addForeignKey('blog_id',  'blogs', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addForeignKey('user_id',  'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->create();
    }
}
