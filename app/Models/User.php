<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'household_id', 'telegram_chat_id', 'telegram_link_code'])]
#[Hidden(['password', 'remember_token', 'telegram_chat_id', 'telegram_link_code'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** Anak-anak dalam keluarga ini (dilihat semua orang tua di keluarga yang sama). */
    public function children(): Builder
    {
        return Child::query()->where('household_id', $this->household_id ?? -1);
    }

    /** Orang tua lain di keluarga yang sama (termasuk diri sendiri). */
    public function householdMembers(): Builder
    {
        return self::query()->where('household_id', $this->household_id ?? -1);
    }

    /** Apakah user ini berhak atas anak tersebut (satu keluarga). */
    public function owns(Child $child): bool
    {
        return $this->household_id !== null && $this->household_id === $child->household_id;
    }

    public function telegramLinked(): bool
    {
        return filled($this->telegram_chat_id);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
