<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S6BlogsSeeder extends AbstractSeed
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

        $blogs = [
            [
                'author_id' => $otonari->id,
                'title'     => 'My First Blog Post',
                'content'   => 'This is the content of my first blog post.',
                'visibility'=> 'public',
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ],
            [
                'author_id' => $simada->id,
                'title'     => 'Hello World',
                'content'   => 'Welcome to my blog! This is my first post.',
                'visibility'=> 'public',
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ],
            [
                'author_id' => $katuhara->id,
                'title'     => 'Tech Trends in 2024',
                'content'   => 'Let’s explore the latest tech trends in 2024.',
                'visibility'=> 'public',
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ],
        ];
        $this->table('blogs')->insert($blogs)->saveData();
    }
}
