<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Catatan penyelesaian tantangan mingguan (satu per anak per minggu). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('week_key', 12);      // mis. 2026-W29
            $table->string('challenge_key', 30);
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->unique(['child_id', 'week_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_completions');
    }
};
