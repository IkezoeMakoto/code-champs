<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // email カラムを login_id にリネーム
            $table->renameColumn('email', 'login_id');
            // email_verified_at カラムを削除（メール認証は使用しない）
            $table->dropColumn('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('login_id', 'email');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};
