<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Item katalog hadiah yang bisa ditukar anak dengan poin. */
#[Fillable(['child_id', 'title', 'emoji', 'cost', 'is_active'])]
class Reward extends Model
{
    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }
}
