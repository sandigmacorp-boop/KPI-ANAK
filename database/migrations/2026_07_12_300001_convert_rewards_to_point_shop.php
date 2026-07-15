<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sistem hadiah bertarget diganti katalog tukar poin.
        Schema::dropIfExists('rewards');

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('emoji', 16)->default('🎁');
            $table->unsignedInteger('cost'); // harga dalam poin
            $table->boolean('is_active')->default(true); // tampil di katalog anak
            $table->timestamps();
        });

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot hadiah saat ditukar, agar riwayat aman walau hadiahnya dihapus/diubah.
            $table->string('title', 80);
            $table->string('emoji', 16);
            $table->unsignedInteger('cost');
            $table->timestamp('delivered_at')->nullable(); // sudah diserahkan orang tua
            $table->timestamp('canceled_at')->nullable();  // dibatalkan → poin kembali
            $table->timestamps();

            $table->index(['child_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('rewards');
    }
};
