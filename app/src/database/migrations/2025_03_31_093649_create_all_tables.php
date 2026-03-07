<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create challenges table
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
        });

        // Create languages table
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create challenge_languages table
        Schema::create('challenge_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->text('sample_code');
            $table->timestamps();
        });

        // Create test_cases table
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->text('input');
            $table->text('expected_output');
            $table->integer('order')->nullable();
            $table->timestamps();
        });

        // Create submissions table
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->text('code');
            $table->integer('score');
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        // Create user_passkeys table
        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('credential_id');
            $table->text('public_key');
            $table->integer('sign_count');
            $table->string('device_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_passkeys');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('test_cases');
        Schema::dropIfExists('challenge_languages');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('challenges');
    }
};
