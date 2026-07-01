<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ReplySchedules extends AbstractMigration
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
        $table = $this->table("reply_schedules");
        $table->addColumn("group_id", "integer", ["null" => false, "signed" => false])
            ->addColumn("chat_id", "integer", ["null" => false, "signed" => false])
            ->addColumn("is_checking", "boolean", ["null" => false, "default" => false])
            ->addForeignKey("group_id", "groups", "id", ["delete" => "cascade"])
            ->addForeignKey("chat_id", "chats", "id", ["delete"=>"cascade"])
            ->addIndex(["group_id"], ["unique"=>true]);

        $table->create();
    }
}
