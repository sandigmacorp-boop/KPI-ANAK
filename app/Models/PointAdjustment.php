<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Penyesuaian poin manual oleh orang tua: bonus (+) atau pelanggaran (−). */
#[Fillable(['child_id', 'amount', 'reason'])]
class PointAdjustment extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'integer'];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function isBonus(): bool
    {
        return $this->amount >= 0;
    }

    public function emoji(): string
    {
        return $this->isBonus() ? '🎉' : '⚠️';
    }

    /** Nilai bertanda untuk ditampilkan, mis. "+20" atau "−10". */
    public function signed(): string
    {
        return ($this->amount >= 0 ? '+' : '−').number_format(abs($this->amount), 0, ',', '.');
    }
}
