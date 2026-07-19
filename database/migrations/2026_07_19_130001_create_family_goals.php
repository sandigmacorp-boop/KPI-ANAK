<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tujuan bersama keluarga: semua anak menyumbang poin ke satu target. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('emoji', 16)->default('🎯');
            $table->unsignedInteger('target');
            $table->timestamp('achieved_at')->nullable(); // terkunci saat target tercapai
            $table->timestamp('claimed_at')->nullable();   // ditandai sudah dirayakan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_goals');
    }
};
