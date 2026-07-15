<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Transaksi penukaran poin → hadiah (menyimpan snapshot hadiah). */
#[Fillable(['child_id', 'reward_id', 'title', 'emoji', 'cost', 'delivered_at', 'canceled_at'])]
class Redemption extends Model
{
    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'delivered_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('delivered_at')->whereNull('canceled_at');
    }

    public function isPending(): bool
    {
        return $this->delivered_at === null && $this->canceled_at === null;
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->canceled_at !== null => 'Dibatalkan (poin kembali)',
            $this->delivered_at !== null => 'Sudah diberikan',
            default => 'Menunggu diberikan',
        };
    }
}
