<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['child_id', 'date', 'mood'])]
class DailyMood extends Model
{
    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
