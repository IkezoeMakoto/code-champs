<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('languages')->insert(
            array_map(
                static fn (string $name): array => ['name' => $name],
                ['php8.5', 'php8.4', 'php7.4', 'node22', 'bash5.2'],
            ),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('languages')
            ->whereIn('name', ['php8.5', 'php8.4', 'php7.4', 'node22', 'bash5.2'])
            ->delete();
    }
};
