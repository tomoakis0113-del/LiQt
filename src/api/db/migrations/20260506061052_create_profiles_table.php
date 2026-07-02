<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProfilesTable extends AbstractMigration
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
        $table = $this->table('profiles', [
            'id' => false,
            'primary_key' => 'user_id'
        ]);

        $table->addColumn('user_id',    'integer',  ['signed' => false])
            ->addColumn('display_name', 'string',   ['limit' => 100, 'null' => false])
            ->addColumn('introduction', 'text',     ['null'  => true])
            ->addColumn('icon_url',     'text',     ['null'  => true])
            ->addColumn('tags',         'text',     ['null'  => true])

            // 外部キー制約
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->create();
    }
}
