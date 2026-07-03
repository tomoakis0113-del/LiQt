<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NoticeBlocks extends AbstractMigration
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
        $table = $this->table("notice_blocks");
        $table->addColumn("user_id", "integer", ["null" => true, "signed" => false])
            ->addColumn("group_id", "integer", ["null" => true, "signed" => false])
            ->addForeignKey("user_id", "users", "id", ["delete" => "cascade"])
            ->addForeignKey("group_id", "groups", "id", ["delete" => "cascade"])
            ->addIndex(["user_id", "group_id"], ["unique"=>true]);

        $table->create();
    }
}
