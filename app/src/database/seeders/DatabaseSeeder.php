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
            'name' => 'admin User',
            'login_id' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'test User',
            'login_id' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Insert master data for languages
        Language::create(['name' => 'php8.4']);
        Language::create(['name' => 'php7.4']);
        Language::create(['name' => 'es2023']);
    }
}
