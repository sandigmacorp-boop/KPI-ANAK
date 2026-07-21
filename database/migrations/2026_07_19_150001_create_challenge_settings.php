<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tantangan pekan yang ditentukan orang tua (menimpa rotasi otomatis).
 * Satu pengaturan per keluarga per pekan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('week_key', 12);           // mis. 2026-W29
            $table->string('challenge_key', 30);      // key preset atau "custom"
            $table->string('emoji', 16)->default('🎯');
            $table->string('title', 60);
            $table->string('desc', 120);
            $table->string('metric', 30);
            $table->unsignedInteger('target');
            $table->unsignedInteger('bonus');
            $table->timestamps();

            $table->unique(['household_id', 'week_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_settings');
    }
};
