<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S1ProfileSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [
            'S0UserSeeder'
        ];
    }

    public function run(): void
    {
        $ai         = models\User::query()->where('user_id', 'ai')->firstOrFail(['id']);
        $otonari    = models\User::query()->where('user_id', 'otonari')->firstOrFail(['id']);
        $simada     = models\User::query()->where('user_id', 'simada')->firstOrFail(['id']);
        $katuhara   = models\User::query()->where('user_id', 'katuhara')->firstOrFail(['id']);

        $profiles = [
            [
                'user_id' => $ai->id,
                'display_name' => 'AI',
                'introduction' => 'AIのプロフィールです。',
            ],
            [
                'user_id' => $otonari->id,
                'display_name' => 'おとなり',
                'introduction' => 'おとなりのプロフィールです。',
            ],
            [
                'user_id' => $simada->id,
                'display_name' => 'しまだ',
                'introduction' => 'しまだのプロフィールです。',
            ],
            [
                'user_id' => $katuhara->id,
                'display_name' => 'かつはら',
                'introduction' => 'かつはらのプロフィールです。',
            ],
        ];
        $this->table('profiles')->insert($profiles)->saveData();
    }
}
