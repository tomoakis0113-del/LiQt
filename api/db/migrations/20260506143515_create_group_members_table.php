<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateGroupMembersTable extends AbstractMigration
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
        $table = $this->table('group_members',[
            'id'=>false,
            'primary_key'=>['group_id','user_id']
        ]);
        $table->addColumn('group_id',   'integer',  ['signed'=>false])
            ->addColumn('user_id',      'integer',  ['signed'=>false])
            ->addColumn('role',         'enum',     ['values'=>['owner','manager','member'],'default'=>'member'])
            ->addColumn('joined_at',    'datetime', ['default'=>'CURRENT_TIMESTAMP'])

            // 外部キー制約
            ->addForeignKey('group_id','groups','id',['delete'=>'cascade'])
            ->addForeignKey('user_id', 'users', 'id',['delete'=>'cascade'])
            ->create();
    }
}
