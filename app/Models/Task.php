<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['child_id', 'title', 'emoji', 'points', 'time_slot', 'requires_photo', 'days', 'is_active', 'sort_order'])]
class Task extends Model
{
    public const SLOTS = [
        'pagi' => ['label' => 'Pagi', 'emoji' => '🌅', 'from' => '04:00', 'until' => '10:59'],
        'siang' => ['label' => 'Siang', 'emoji' => '☀️', 'from' => '11:00', 'until' => '14:59'],
        'sore' => ['label' => 'Sore', 'emoji' => '🌤️', 'from' => '15:00', 'until' => '18:29'],
        'malam' => ['label' => 'Malam', 'emoji' => '🌙', 'from' => '18:30', 'until' => '23:59'],
    ];

    /** Slot waktu yang sedang berjalan sekarang (null di luar jam 04:00–23:59). */
    public static function currentSlot(?\Carbon\CarbonInterface $at = null): ?string
    {
        $time = ($at ?? now())->format('H:i');

        foreach (self::SLOTS as $key => $slot) {
            if ($time >= $slot['from'] && $time <= $slot['until']) {
                return $key;
            }
        }

        return null;
    }

    /** Apakah jam batas ("until") slot tugas ini untuk hari ini sudah lewat dibanding waktu sekarang. */
    public function isSlotOver(?\Carbon\CarbonInterface $at = null): bool
    {
        $slot = self::SLOTS[$this->time_slot] ?? null;

        return $slot !== null && ($at ?? now())->format('H:i') > $slot['until'];
    }

    public const DAY_NAMES = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];

    protected function casts(): array
    {
        return [
            'days' => 'array',
            'is_active' => 'boolean',
            'requires_photo' => 'boolean',
            'points' => 'integer',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /** Apakah tugas ini terjadwal pada tanggal tersebut. */
    public function isScheduledOn(CarbonInterface $date): bool
    {
        return empty($this->days) || in_array($date->isoWeekday(), $this->days);
    }

    public function slotOrder(): int
    {
        $order = array_search($this->time_slot, array_keys(self::SLOTS));

        return $order === false ? 99 : $order;
    }

    public function slotLabel(): string
    {
        $slot = self::SLOTS[$this->time_slot] ?? null;

        return $slot ? $slot['emoji'].' '.$slot['label'] : $this->time_slot;
    }

    /** Ringkasan jadwal: "Setiap hari" atau "Sen, Rab, Jum". */
    public function daysLabel(): string
    {
        if (empty($this->days)) {
            return 'Setiap hari';
        }

        $days = collect($this->days)->sort()->map(fn ($d) => self::DAY_NAMES[$d] ?? $d);

        return $days->implode(', ');
    }
}
