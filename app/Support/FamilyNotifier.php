<?php

namespace App\Support;

use App\Models\Child;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonInterface;

/** Menyusun teks pesan pengingat & rekap untuk dikirim ke Telegram orang tua. */
class FamilyNotifier
{
    /**
     * Pesan pengingat tugas slot berjalan untuk seorang orang tua
     * (mencakup semua anaknya yang masih punya tugas belum selesai).
     * Null bila tidak ada yang perlu diingatkan.
     */
    public static function slotReminderText(User $user, string $slot, CarbonInterface $date): ?string
    {
        $slotLabel = Task::SLOTS[$slot]['label'] ?? $slot;
        $slotEmoji = Task::SLOTS[$slot]['emoji'] ?? '⏰';

        $lines = [];
        foreach ($user->children()->orderBy('created_at')->get() as $child) {
            $pending = $child->pendingTasksInSlot($slot, $date);
            if ($pending->isEmpty()) {
                continue;
            }

            $lines[] = "\n{$child->emoji} {$child->name} — {$pending->count()} tugas belum:";
            foreach ($pending->take(6) as $task) {
                $lines[] = "  • {$task->emoji} {$task->title} ({$task->points} poin)";
            }
        }

        if (empty($lines)) {
            return null;
        }

        return "{$slotEmoji} Pengingat Tugas {$slotLabel}".implode("\n", $lines)
            ."\n\nYuk diselesaikan lalu centang di aplikasi. 💪";
    }

    /**
     * Rekap 7 hari terakhir untuk seorang orang tua (semua anaknya).
     * Null bila tidak ada anak dengan data minggu ini.
     */
    public static function weeklyRecapText(User $user, CarbonInterface $today): ?string
    {
        $from = $today->copy()->subDays(6);

        $blocks = [];
        foreach ($user->children()->orderBy('created_at')->get() as $child) {
            $stats = $child->dailyStats($from, $today);
            $active = collect($stats)->filter(fn ($d) => $d['percent'] !== null);
            if ($active->isEmpty()) {
                continue;
            }

            $avg = (int) round($active->avg('percent'));
            $earned = collect($stats)->sum('earned_points');
            $doneTasks = collect($stats)->sum('done_tasks');
            $threeStar = $active->filter(fn ($d) => $d['percent'] >= Child::STAR_3)->count();

            $blocks[] = "{$child->emoji} {$child->name}\n"
                ."  • KPI rata-rata: {$avg}%\n"
                ."  • Tugas selesai: {$doneTasks}\n"
                ."  • Poin didapat: {$earned}\n"
                ."  • Hari bintang-3: {$threeStar}\n"
                ."  • 🔥 Streak: {$child->streak()} hari · 🏅 Saldo: {$child->pointsBalance()} poin";
        }

        if (empty($blocks)) {
            return null;
        }

        $range = $from->translatedFormat('j M').' – '.$today->translatedFormat('j M Y');

        return "📊 Rekap Mingguan SANS FAMILY\n{$range}\n\n".implode("\n\n", $blocks)
            ."\n\nBuka aplikasi untuk melihat detail & foto bukti. 👍";
    }
}
