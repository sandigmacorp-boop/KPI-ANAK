<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['task_id', 'child_id', 'date', 'completed_at', 'photo_path'])]
class TaskCompletion extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
