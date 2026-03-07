<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Language;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // bash5.2を言語テーブルに追加
        Language::create([
            'name' => 'bash5.2',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // bash5.2を言語テーブルから削除
        Language::where('name', 'bash5.2')->delete();
    }
};