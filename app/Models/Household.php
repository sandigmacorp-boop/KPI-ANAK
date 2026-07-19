<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Satu keluarga: menampung beberapa orang tua & beberapa anak. */
#[Fillable(['name'])]
class Household extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(FamilyGoal::class);
    }

    /** Tujuan keluarga yang sedang berjalan (belum dirayakan), bila ada. */
    public function activeGoal(): ?FamilyGoal
    {
        return $this->goals()->whereNull('claimed_at')->latest()->first();
    }
}
