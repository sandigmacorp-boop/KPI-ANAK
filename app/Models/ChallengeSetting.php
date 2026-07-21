<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tantangan pekan pilihan orang tua (menimpa rotasi otomatis). */
#[Fillable(['household_id', 'week_key', 'challenge_key', 'emoji', 'title', 'desc', 'metric', 'target', 'bonus'])]
class ChallengeSetting extends Model
{
    protected function casts(): array
    {
        return ['target' => 'integer', 'bonus' => 'integer'];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** Bentuk array seragam seperti preset di WeeklyChallenge. */
    public function toChallenge(): array
    {
        return [
            'key' => $this->challenge_key,
            'emoji' => $this->emoji,
            'title' => $this->title,
            'desc' => $this->desc,
            'metric' => $this->metric,
            'target' => $this->target,
            'bonus' => $this->bonus,
            'custom' => true,
        ];
    }
}
