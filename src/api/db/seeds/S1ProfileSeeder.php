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

        $profiles = [
            [
                'user_id' => $ai->id,
                'display_name' => 'AI',
                'icon_url' => 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgEkdARmw-HyAXpmnhrr9J9YEkUdJA5DLdt0UrgsXey80KFFAFhh1LsabZpEWtSiDNa1Itao7snbVnwSJB3cJOXM98aCUigACkOeOnquX8apCXJyx7bE6sPyoKOw6zDpQvVeXswG8LLgdaoAdztRkZX4bjM-oI6b1lt9lZJlMZqY-2wX1tMGFnIK_b8QMGu/s658/ai_character04_laugh.png',
                'introduction' => 'AIのプロフィールです。',
            ],
        ];
        $this->table('profiles')->insert($profiles)->saveData();
    }
}
