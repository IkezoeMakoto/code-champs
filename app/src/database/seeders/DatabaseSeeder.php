<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Language;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            [
                'name' => 'admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ].
            [
                'name' => 'test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ]
        ]);

        // Insert master data for languages
        Language::create([
            [
                'name' => 'php8.4', // insert into languages (`name`) values ('php8.4');
            ],
            [
                'name' => 'php7.4', // insert into languages (`name`) values ('php7.4');
            ],
            [
                'name' => 'es2023', // insert into languages (`name`) values ('es2023');
            ]
        ]);
    }
}
