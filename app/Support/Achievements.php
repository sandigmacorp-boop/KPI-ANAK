<?php

namespace App\Support;

/** Katalog lencana/pencapaian: tiap lencana punya metrik & ambang. */
class Achievements
{
    /** key => [emoji, title, desc, metric, threshold] */
    public const LIST = [
        'tugas-10' => ['emoji' => '🎯', 'title' => 'Pemula', 'desc' => 'Selesaikan 10 tugas', 'metric' => 'lifetime_tasks', 'threshold' => 10],
        'tugas-50' => ['emoji' => '🏃', 'title' => 'Rajin', 'desc' => 'Selesaikan 50 tugas', 'metric' => 'lifetime_tasks', 'threshold' => 50],
        'tugas-100' => ['emoji' => '💪', 'title' => 'Juara Tugas', 'desc' => 'Selesaikan 100 tugas', 'metric' => 'lifetime_tasks', 'threshold' => 100],
        'tugas-500' => ['emoji' => '🏆', 'title' => 'Legenda Tugas', 'desc' => 'Selesaikan 500 tugas', 'metric' => 'lifetime_tasks', 'threshold' => 500],
        'streak-3' => ['emoji' => '🔥', 'title' => 'Panas!', 'desc' => '3 hari beruntun', 'metric' => 'streak', 'threshold' => 3],
        'streak-7' => ['emoji' => '☄️', 'title' => 'Seminggu Penuh', 'desc' => '7 hari beruntun', 'metric' => 'streak', 'threshold' => 7],
        'streak-30' => ['emoji' => '🌟', 'title' => 'Sebulan Konsisten', 'desc' => '30 hari beruntun', 'metric' => 'streak', 'threshold' => 30],
        'sempurna-1' => ['emoji' => '⭐', 'title' => 'Hari Sempurna', 'desc' => '1 hari KPI 100%', 'metric' => 'perfect_days', 'threshold' => 1],
        'sempurna-10' => ['emoji' => '✨', 'title' => 'Bintang Sejati', 'desc' => '10 hari KPI 100%', 'metric' => 'perfect_days', 'threshold' => 10],
        'poin-500' => ['emoji' => '🏅', 'title' => 'Kolektor Poin', 'desc' => 'Kumpulkan 500 poin', 'metric' => 'lifetime_points', 'threshold' => 500],
        'poin-2000' => ['emoji' => '💰', 'title' => 'Sultan Poin', 'desc' => 'Kumpulkan 2000 poin', 'metric' => 'lifetime_points', 'threshold' => 2000],
        'pet-dewasa' => ['emoji' => '🐲', 'title' => 'Sahabat Setia', 'desc' => 'Besarkan peliharaan ke tahap Dewasa', 'metric' => 'pet_stage', 'threshold' => 3],
        'pet-max' => ['emoji' => '🌈', 'title' => 'Peliharaan Legendaris', 'desc' => 'Peliharaan mencapai tahap tertinggi', 'metric' => 'pet_stage', 'threshold' => 4],
    ];

    public static function get(string $key): ?array
    {
        return self::LIST[$key] ?? null;
    }
}
