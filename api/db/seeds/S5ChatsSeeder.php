<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S5ChatsSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['S0UserSeeder', 'S2GroupsSeeder'];
    }

    public function run(): void
    {
        $otonari = models\User::query()->where('user_id', 'otonari')->firstOrFail(['id']);
        $group1 = $this->fetchRow('SELECT id FROM groups WHERE name = "General Chat"');

        $chats = [
            [
                'group_id' => $group1['id'],
                'sender_id' => $otonari->id,
                'content' => 'Hello everyone!',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];
        $this->table('chats')->insert($chats)->saveData();
    }
}
