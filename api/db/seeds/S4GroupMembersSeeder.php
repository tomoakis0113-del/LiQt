<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S4GroupMembersSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['S0UserSeeder', 'S2GroupsSeeder'];
    }

    public function run(): void
    {
        $otonari = models\User::query()->where('user_id', 'otonari')->firstOrFail(['id']);
        $simada = models\User::query()->where('user_id', 'simada')->firstOrFail(['id']);
        $group1 = $this->fetchRow('SELECT id FROM groups WHERE name = "General Chat"');
        $group2 = $this->fetchRow('SELECT id FROM groups WHERE name = "Private Club"');

        $members = [
            [
                'group_id' => $group1['id'],
                'user_id' => $otonari->id,
                'role' => 'owner',
                'joined_at' => date('Y-m-d H:i:s'),
            ],
            [
                'group_id' => $group1['id'],
                'user_id' => $simada->id,
                'role' => 'member',
                'joined_at' => date('Y-m-d H:i:s'),
            ],
            [
                'group_id' => $group2['id'],
                'user_id' => $simada->id,
                'role' => 'owner',
                'joined_at' => date('Y-m-d H:i:s'),
            ]
        ];
        $this->table('group_members')->insert($members)->saveData();
    }
}
