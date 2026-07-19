<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Perasaan (mood) harian anak — satu per anak per hari. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('mood', 20);
            $table->timestamps();

            $table->unique(['child_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_moods');
    }
};
