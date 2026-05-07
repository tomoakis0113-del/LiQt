<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S8CommentsSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['S0UserSeeder', 'S6BlogsSeeder'];
    }

    public function run(): void
    {
        $simada = models\User::query()->where('user_id', 'simada')->firstOrFail(['id']);
        $blog = $this->fetchRow('SELECT id FROM blogs LIMIT 1');

        $comments = [
            [
                'blog_id' => $blog['id'],
                'user_id' => $simada->id,
                'content' => 'Great post!',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];
        $this->table('blog_comments')->insert($comments)->saveData();
    }
}
