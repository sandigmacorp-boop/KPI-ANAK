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

    public function challengeSettings(): HasMany
    {
        return $this->hasMany(ChallengeSetting::class);
    }

    /** Tantangan efektif pekan ini: pilihan orang tua bila ada, selain itu rotasi otomatis. */
    public function challengeForWeek(?string $weekKey = null): array
    {
        $weekKey ??= \App\Support\WeeklyChallenge::weekKey();
        $setting = $this->challengeSettings()->where('week_key', $weekKey)->first();

        return $setting
            ? $setting->toChallenge()
            : \App\Support\WeeklyChallenge::current() + ['custom' => false];
    }

    public function teamChallenges(): HasMany
    {
        return $this->hasMany(TeamChallenge::class);
    }
}
