<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSessionTable extends AbstractMigration
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
        $table = $this->table('session');
        $table->addColumn('user_id',    'integer',  ['null'=>false,'signed'=>false])
            ->addColumn('token',        'string',   ['null'=>false,'limit'=>255])
            ->addColumn('expire',       'datetime', ['null'=>false])
            ->addColumn('user_agent',   'text',     ['null'=>false])

            // Unique制約を追加
            ->addIndex(['token'],['unique'=>true])

            // 外部キー制約を追加
            ->addForeignKey('user_id','users','id',['delete'=>'cascade'])
            ->create();
    }
}
