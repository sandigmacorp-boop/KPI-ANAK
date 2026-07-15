<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('requires_photo')->default(true)->after('time_slot');
        });

        Schema::table('task_completions', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('completed_at');
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('emoji', 16)->default('🎁');
            $table->string('type', 10); // streak | points
            $table->unsignedInteger('target');
            $table->timestamp('achieved_at')->nullable(); // terkunci saat pertama kali tercapai
            $table->timestamp('claimed_at')->nullable();  // ditandai "sudah diberikan" oleh orang tua
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');

        Schema::table('task_completions', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('requires_photo');
        });
    }
};
