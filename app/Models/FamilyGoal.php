<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/** Target poin bersama seluruh anak dalam satu keluarga. */
#[Fillable(['household_id', 'title', 'emoji', 'target', 'achieved_at', 'claimed_at'])]
class FamilyGoal extends Model
{
    protected function casts(): array
    {
        return [
            'target' => 'integer',
            'achieved_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** Poin per anak yang menyumbang sejak tujuan dibuat. */
    public function contributions(): Collection
    {
        return $this->household->children()->orderBy('created_at')->get()->map(function (Child $child) {
            $points = (int) $child->completions()
                ->join('tasks', 'tasks.id', '=', 'task_completions.task_id')
                ->where('task_completions.created_at', '>=', $this->created_at)
                ->sum('tasks.points');

            return ['child' => $child, 'points' => $points];
        });
    }

    /** Total poin terkumpul menuju target. */
    public function progress(): int
    {
        return (int) $this->contributions()->sum('points');
    }

    public function percent(): int
    {
        return $this->target > 0 ? min(100, (int) round($this->progress() / $this->target * 100)) : 0;
    }

    public function isAchieved(): bool
    {
        return $this->achieved_at !== null || $this->progress() >= $this->target;
    }

    /** Kunci status tercapai; kembalikan true bila BARU tercapai sekarang. */
    public function refreshAchieved(): bool
    {
        if ($this->achieved_at === null && $this->progress() >= $this->target) {
            $this->update(['achieved_at' => now()]);

            return true;
        }

        return false;
    }
}
