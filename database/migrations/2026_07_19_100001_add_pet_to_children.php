<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Peliharaan virtual per anak yang tumbuh seiring poin (XP) yang dikumpulkan.
 * pet_xp = titik tertinggi poin tugas seumur hidup (tidak pernah menyusut).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('pet_type', 20)->default('naga')->after('color');
            $table->unsignedInteger('pet_xp')->default(0)->after('pet_type');
        });

        // Backfill: XP awal = total poin tugas yang sudah pernah diselesaikan.
        foreach (DB::table('children')->pluck('id') as $childId) {
            $xp = DB::table('task_completions')
                ->join('tasks', 'tasks.id', '=', 'task_completions.task_id')
                ->where('task_completions.child_id', $childId)
                ->sum('tasks.points');
            DB::table('children')->where('id', $childId)->update(['pet_xp' => (int) $xp]);
        }
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['pet_type', 'pet_xp']);
        });
    }
};
