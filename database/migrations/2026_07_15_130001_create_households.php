<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rumah tangga (keluarga): wadah agar beberapa orang tua bisa memantau anak yang sama.
 * Pendekatan aman untuk SQLite: hanya menambah kolom + backfill (tanpa rebuild tabel).
 * Kolom lama children.user_id dipertahankan sebagai penanda "pembuat".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->default('Keluarga');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('children', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        // Backfill: satu rumah tangga per orang tua yang ada; tautkan anak-anaknya.
        foreach (DB::table('users')->whereNull('household_id')->get() as $user) {
            $householdId = DB::table('households')->insertGetId([
                'name' => 'Keluarga',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $user->id)->update(['household_id' => $householdId]);
            DB::table('children')->where('user_id', $user->id)->update(['household_id' => $householdId]);
        }
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropConstrainedForeignId('household_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('household_id');
        });
        Schema::dropIfExists('households');
    }
};
