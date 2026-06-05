<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
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
        $table = $this->table('users');
        $table->addColumn('user_id',    'string',   ['limit' => 50])  // ユーザーが指定する一意のID (英数字)
            ->addColumn('mail_address', 'string',   ['limit' => 255])
            ->addColumn('password',     'string',   ['limit' => 255]) // ハッシュ化されたパスワード
            ->addColumn('is_active',    'boolean',  ['default' => false])
            ->addColumn('created_at',   'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',   'datetime', ['null' => true]) // 更新前は空を許容する

            // UNIQUE制約
            ->addIndex(['user_id'],      ['unique' => true])
            ->addIndex(['mail_address'], ['unique' => true])
            ->create();
    }
}
