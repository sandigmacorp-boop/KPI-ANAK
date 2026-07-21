<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tantangan kerja sama tim: satu misi untuk seluruh anak dalam keluarga. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('emoji', 16)->default('🤝');
            $table->string('description', 200)->nullable();
            $table->unsignedInteger('points');
            $table->string('status', 10)->default('open'); // open | pending | approved
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('team_challenge_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete(); // pengirim laporan
            $table->string('note', 200)->nullable();
            $table->string('status', 10)->default('pending'); // pending | approved | rejected
            $table->string('review_note', 200)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('team_challenge_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('team_challenge_submissions')->cascadeOnDelete();
            $table->string('photo_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_challenge_photos');
        Schema::dropIfExists('team_challenge_submissions');
        Schema::dropIfExists('team_challenges');
    }
};
