<?php

namespace App\Models;

use App\Support\ProofPhoto;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

#[Fillable(['user_id', 'name', 'emoji', 'color', 'access_token'])]
class Child extends Model
{
    /** Ambang KPI (persen) untuk bintang & streak. */
    public const STAR_3 = 90;
    public const STAR_2 = 70;
    public const STAR_1 = 40;
    public const STREAK_MIN = 80;

    /** Palet identitas anak — urutan tervalidasi aman buta-warna (validate_palette.js). */
    public const COLORS = ['#7C3AED', '#D97706', '#0D9488', '#DB2777', '#0369A1', '#65A30D'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PointAdjustment::class);
    }

    /** Tugas aktif yang terjadwal pada tanggal tertentu, urut sesuai waktu. */
    public function tasksForDate(CarbonInterface $date)
    {
        return $this->tasks()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Task $t) => $t->isScheduledOn($date))
            ->sortBy([
                fn (Task $a, Task $b) => $a->slotOrder() <=> $b->slotOrder(),
                fn (Task $a, Task $b) => $a->sort_order <=> $b->sort_order,
                fn (Task $a, Task $b) => $a->id <=> $b->id,
            ])
            ->values();
    }

    /**
     * Statistik harian (KPI) untuk rentang tanggal, dihitung sekali jalan.
     *
     * @return array<string, array{date: CarbonInterface, percent: ?int, total_points: int, earned_points: int, total_tasks: int, done_tasks: int, done_ids: array}>
     */
    public function dailyStats(CarbonInterface $from, CarbonInterface $to): array
    {
        $tasks = $this->tasks()->where('is_active', true)->get();

        $completions = $this->completions()
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get()
            ->groupBy(fn (TaskCompletion $c) => $c->date->toDateString());

        $days = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        $registeredAt = $this->created_at?->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            // Hari sebelum anak terdaftar dianggap tanpa data, bukan 0%.
            $scheduled = ($registeredAt && $cursor->lt($registeredAt))
                ? collect()
                : $tasks->filter(fn (Task $t) => $t->isScheduledOn($cursor));
            $dayCompletions = $completions[$key] ?? collect();
            $doneIds = $dayCompletions->pluck('task_id')->all();
            $done = $scheduled->whereIn('id', $doneIds);

            $total = (int) $scheduled->sum('points');
            $earned = (int) $done->sum('points');

            $days[$key] = [
                'date' => $cursor->copy(),
                'percent' => $total > 0 ? (int) round($earned / $total * 100) : null,
                'total_points' => $total,
                'earned_points' => $earned,
                'total_tasks' => $scheduled->count(),
                'done_tasks' => $done->count(),
                'done_ids' => $doneIds,
                'photos' => $dayCompletions->whereNotNull('photo_path')->pluck('photo_path', 'task_id')->all(),
            ];

            $cursor = $cursor->copy()->addDay();
        }

        return $days;
    }

    public function statsForDate(CarbonInterface $date): array
    {
        return $this->dailyStats($date, $date)[$date->toDateString()];
    }

    /** Jumlah bintang (0-3) untuk sebuah persentase KPI; null bila tidak ada tugas. */
    public static function starsFor(?int $percent): ?int
    {
        if ($percent === null) {
            return null;
        }

        return match (true) {
            $percent >= self::STAR_3 => 3,
            $percent >= self::STAR_2 => 2,
            $percent >= self::STAR_1 => 1,
            default => 0,
        };
    }

    /** Rentetan hari beruntun dengan KPI >= STREAK_MIN (hari ini yang belum tuntas tidak memutus). */
    public function streak(): int
    {
        if ($this->tasks()->where('is_active', true)->count() === 0) {
            return 0;
        }

        $stats = $this->dailyStats(today()->subDays(179), today());
        $streak = 0;
        $isToday = true;

        foreach (array_reverse($stats, true) as $day) {
            if ($day['percent'] !== null) {
                if ($day['percent'] >= self::STREAK_MIN) {
                    $streak++;
                } elseif (! $isToday) {
                    break;
                }
            }
            $isToday = false;
        }

        return $streak;
    }

    /** Centang / batalkan sebuah tugas pada tanggal tertentu (opsional dengan foto bukti). */
    public function toggleTask(Task $task, CarbonInterface $date, ?UploadedFile $photo = null): array
    {
        $existing = $this->completions()
            ->where('task_id', $task->id)
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($existing) {
            ProofPhoto::delete($existing->photo_path);
            $existing->delete();
            $done = false;
            $photoPath = null;
        } else {
            $photoPath = $photo ? ProofPhoto::store($photo, $this) : null;
            $this->completions()->firstOrCreate(
                ['task_id' => $task->id, 'date' => $date->toDateString()],
                ['completed_at' => now(), 'photo_path' => $photoPath],
            );
            $done = true;
        }

        return ['done' => $done, 'stats' => $this->statsForDate($date), 'photo_path' => $photoPath];
    }

    /** Toggle tugas lalu kembalikan payload JSON siap pakai untuk front-end. */
    public function togglePayload(Task $task, CarbonInterface $date, ?UploadedFile $photo = null): array
    {
        $result = $this->toggleTask($task, $date, $photo);
        $stats = $result['stats'];

        return [
            'done' => $result['done'],
            'percent' => $stats['percent'],
            'earned_points' => $stats['earned_points'],
            'total_points' => $stats['total_points'],
            'done_tasks' => $stats['done_tasks'],
            'total_tasks' => $stats['total_tasks'],
            'stars' => self::starsFor($stats['percent']),
            'all_done' => $stats['total_tasks'] > 0 && $stats['done_tasks'] === $stats['total_tasks'],
            'photo_url' => ProofPhoto::url($result['photo_path']),
            'balance' => $this->pointsBalance(),
        ];
    }

    /** Total poin sepanjang masa (dasar target hadiah tipe "points"). */
    public function totalPoints(): int
    {
        return (int) $this->completions()
            ->join('tasks', 'tasks.id', '=', 'task_completions.task_id')
            ->sum('tasks.points');
    }

    /** Poin yang sudah dipakai menukar hadiah (penukaran batal tidak dihitung). */
    public function pointsSpent(): int
    {
        return (int) $this->redemptions()->whereNull('canceled_at')->sum('cost');
    }

    /** Total penyesuaian manual (bonus positif − pelanggaran negatif). */
    public function adjustmentsTotal(): int
    {
        return (int) $this->adjustments()->sum('amount');
    }

    /** Saldo poin yang bisa ditukar = poin tugas + penyesuaian − yang ditukar. */
    public function pointsBalance(): int
    {
        return $this->totalPoints() + $this->adjustmentsTotal() - $this->pointsSpent();
    }

    /**
     * Rincian saldo untuk ditampilkan.
     *
     * @return array{from_tasks:int, bonus:int, penalty:int, spent:int, balance:int}
     */
    public function pointsBreakdown(): array
    {
        $fromTasks = $this->totalPoints();
        $bonus = (int) $this->adjustments()->where('amount', '>', 0)->sum('amount');
        $penalty = (int) $this->adjustments()->where('amount', '<', 0)->sum('amount'); // negatif atau 0
        $spent = $this->pointsSpent();

        return [
            'from_tasks' => $fromTasks,
            'bonus' => $bonus,
            'penalty' => $penalty,
            'spent' => $spent,
            'balance' => $fromTasks + $bonus + $penalty - $spent,
        ];
    }

    public function canAfford(Reward $reward): bool
    {
        return $this->pointsBalance() >= $reward->cost;
    }

    /** Tukar poin dengan hadiah — simpan snapshot agar riwayat aman. */
    public function redeem(Reward $reward): Redemption
    {
        return $this->redemptions()->create([
            'reward_id' => $reward->id,
            'title' => $reward->title,
            'emoji' => $reward->emoji,
            'cost' => $reward->cost,
        ]);
    }

    /** Tugas pada slot waktu tertentu hari ini yang belum dicentang (bahan pengingat). */
    public function pendingTasksInSlot(?string $slot, CarbonInterface $date)
    {
        if ($slot === null) {
            return collect();
        }

        $doneIds = $this->completions()
            ->whereDate('date', $date->toDateString())
            ->pluck('task_id')
            ->all();

        return $this->tasksForDate($date)
            ->where('time_slot', $slot)
            ->reject(fn (Task $t) => in_array($t->id, $doneIds))
            ->values();
    }

    public function kidUrl(): string
    {
        return route('kid.show', $this->access_token);
    }
}
