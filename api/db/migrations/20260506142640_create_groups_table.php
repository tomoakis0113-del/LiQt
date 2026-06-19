<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateGroupsTable extends AbstractMigration
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
        $table = $this->table('groups');
        $table->addColumn('name',           'string',   ['limit'=>255,'null'=>false])
            ->addColumn('group_icon_url',   'string',   ['limit'=>255])
            ->addColumn('is_public',        'boolean',  ['default'=>false])
            ->addColumn('created_at',       'datetime', ['default'=>'CURRENT_TIMESTAMP'])

            // UNIQUE制約
            ->addIndex(['name'],['unique'=>true])
            ->create();
    }
}
