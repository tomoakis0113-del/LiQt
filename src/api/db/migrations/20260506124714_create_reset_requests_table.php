<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateResetRequestsTable extends AbstractMigration
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
        $table = $this->table('reset_requests', [
            'id' => false,
            'primary_key' => 'user_id'
        ]);
        $table->addColumn('user_id',    'integer',  ['signed' => false])
            ->addColumn('token',        'string',   ['limit' => 255, 'null' => false])
            ->addColumn('created_at',   'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('expires_at',   'datetime', ['null' => false])

            // 外部キー制約
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->create();
    }
}
