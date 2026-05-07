<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S3BlockListSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [
            'S0UserSeeder'
        ];
    }

    public function run(): void
    {
        $otonari    = models\User::query()->where('user_id', 'otonari')->firstOrFail(['id']);
        $simada     = models\User::query()->where('user_id', 'simada')->firstOrFail(['id']);
        $katuhara   = models\User::query()->where('user_id', 'katuhara')->firstOrFail(['id']);

        $blocks = [
            [
                'user_id'       => $otonari->id,
                'blocked_user_id' => $simada->id,
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'       => $simada->id,
                'blocked_user_id' => $katuhara->id,
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'       => $katuhara->id,
                'blocked_user_id' => $otonari->id,
                'created_at'    => date('Y-m-d H:i:s'),
            ],
        ];
        $this->table('block_list')->insert($blocks)->saveData();
    }
}
