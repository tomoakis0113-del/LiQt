<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S2GroupsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $groups = [
            [
                'name' => 'General Chat',
                'is_public' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'group_icon_url' => '',
            ],
            [
                'name' => 'Private Club',
                'is_public' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'group_icon_url' => '',
            ]
        ];
        $this->table('groups')->insert($groups)->saveData();
    }
}
