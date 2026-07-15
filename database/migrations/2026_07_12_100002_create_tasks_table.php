<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('emoji', 16)->default('⭐');
            $table->unsignedSmallInteger('points')->default(10);
            $table->string('time_slot', 10)->default('pagi'); // pagi | siang | sore | malam
            $table->json('days')->nullable(); // null/[] = setiap hari, selain itu array isoWeekday 1-7
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
