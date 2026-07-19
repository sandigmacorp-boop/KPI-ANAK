<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Tantangan mingguan yang berganti otomatis tiap pekan (rotasi berbasis nomor pekan ISO). */
class WeeklyChallenge
{
    /** Tiap tantangan: key, emoji, title, desc, metric mingguan, target, bonus poin. */
    public const LIST = [
        ['key' => 'rajin', 'emoji' => '🏃', 'title' => 'Minggu Rajin', 'desc' => 'Selesaikan 25 tugas pekan ini', 'metric' => 'weekly_tasks', 'target' => 25, 'bonus' => 40],
        ['key' => 'kolektor', 'emoji' => '💰', 'title' => 'Pemburu Poin', 'desc' => 'Kumpulkan 200 poin pekan ini', 'metric' => 'weekly_points', 'target' => 200, 'bonus' => 40],
        ['key' => 'sempurna', 'emoji' => '⭐', 'title' => 'Kejar Sempurna', 'desc' => 'Raih 3 hari KPI 100% pekan ini', 'metric' => 'weekly_perfect', 'target' => 3, 'bonus' => 50],
        ['key' => 'fotografer', 'emoji' => '📸', 'title' => 'Fotografer Cilik', 'desc' => 'Kirim 8 foto bukti pekan ini', 'metric' => 'weekly_photos', 'target' => 8, 'bonus' => 30],
    ];

    public static function current(?CarbonInterface $date = null): array
    {
        $week = (int) ($date ?? today())->format('W'); // nomor pekan ISO 01-53

        return self::LIST[$week % count(self::LIST)];
    }

    /** Kunci pekan unik, mis. "2026-W29". */
    public static function weekKey(?CarbonInterface $date = null): string
    {
        return ($date ?? today())->format('o-\WW');
    }
}
