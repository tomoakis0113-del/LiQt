<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class S0UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $users = [
            [
                'user_id'       => 'ai',
                'mail_address'  => 'shunji@otonari.dev',
                'password'      => password_hash('Password123', PASSWORD_DEFAULT),
                'is_active'     => true,
            ],
            [
                'user_id'       => 'otonari',
                'mail_address'  => 'example1@example.com',
                'password'      => password_hash('Password123', PASSWORD_DEFAULT),
                'is_active'     => true,
            ],
            [
                'user_id'       => 'simada',
                'mail_address'  => 'example2@example.com',
                'password'      => password_hash('Password123', PASSWORD_DEFAULT),
                'is_active'     => true,
            ],
            [
                'user_id'       => 'katuhara',
                'mail_address'  => 'example3@example.com',
                'password'      => password_hash('Password123', PASSWORD_DEFAULT),
                'is_active'     => true,
            ],
        ];

        $this->table('users')->insert($users)->saveData();
    }
}
