<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NoticeSchedules extends AbstractMigration
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
        $table = $this->table("notice_schedules");
        $table->addColumn("user_id", "integer", ["null" => true, "signed" => false])
            ->addColumn("content", "text", ["null" => true])
            ->addColumn("is_checking", "boolean", ["null" => false, "default" => false])
            ->addForeignKey("user_id", "users", "id", ["delete" => "cascade"]);

        $table->create();
    }
}
