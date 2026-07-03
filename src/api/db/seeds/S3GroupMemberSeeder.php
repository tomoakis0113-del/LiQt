<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S3GroupMemberSeeder extends AbstractSeed
{
    public function run(): void
    {
        $ai = models\User::query()->where('user_id', 'ai')->firstOrFail(['id']);
        $group1 = $this->fetchRow('SELECT id FROM groups WHERE name = "General Chat"');

        $members = [
            [
                'group_id' => $group1['id'],
                'user_id' => $ai->id,
                'role' => 'owner',
                'joined_at' => date('Y-m-d H:i:s'),
            ]
        ];
        $this->table('group_members')->insert($members)->saveData();
    }
}