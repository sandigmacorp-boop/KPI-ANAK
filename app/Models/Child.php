<?php

namespace App\Models;

use App\Support\Achievements;
use App\Support\Pet;
use App\Support\ProofPhoto;
use App\Support\WeeklyChallenge;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

#[Fillable(['user_id', 'household_id', 'name', 'emoji', 'color', 'pet_type', 'pet_xp', 'access_token'])]
class Child extends Model
{
    /** Ambang KPI (persen) untuk bintang & streak. */
    public const STAR_3 = 90;
    public const STAR_2 = 70;
    public const STAR_1 = 40;
    public const STREAK_MIN = 80;

    /** Palet identitas anak — urutan tervalidasi aman buta-warna (validate_palette.js). */
    public const COLORS = ['#7C3AED', '#D97706', '#0D9488', '#DB2777', '#0369A1', '#65A30D'];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** Tujuan keluarga aktif (berbagi dengan saudara sekeluarga). */
    public function familyGoal(): ?FamilyGoal
    {
        return $this->household?->activeGoal();
    }

    /** Orang tua yang menambahkan anak ini (informasional). */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function achievements(): HasMany
    {
        return $this->hasMany(ChildAchievement::class);
    }

    public function moods(): HasMany
    {
        return $this->hasMany(DailyMood::class);
    }

    public function challengeCompletions(): HasMany
    {
        return $this->hasMany(ChallengeCompletion::class);
    }

    /** Metrik pekan berjalan (Sen–hari ini) untuk tantangan mingguan. */
    public function weeklyChallengeMetrics(): array
    {
        $start = today()->startOfWeek();
        $completions = $this->completions()
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', today()->toDateString())
            ->get();

        $stats = $this->dailyStats($start, today());

        return [
            'weekly_tasks' => $completions->count(),
            'weekly_points' => (int) collect($stats)->sum('earned_points'),
            'weekly_perfect' => collect($stats)->filter(fn ($d) => $d['percent'] === 100)->count(),
            'weekly_photos' => $completions->whereNotNull('photo_path')->count(),
        ];
    }

    /** Progres tantangan pekan ini untuk ditampilkan. */
    public function weeklyChallengeProgress(): array
    {
        $ch = WeeklyChallenge::current();
        $metrics = $this->weeklyChallengeMetrics();
        $value = min((int) ($metrics[$ch['metric']] ?? 0), $ch['target']);

        return $ch + [
            'value' => $value,
            'percent' => (int) round($value / max($ch['target'], 1) * 100),
            'completed' => $this->challengeCompletions()->where('week_key', WeeklyChallenge::weekKey())->exists(),
        ];
    }

    /** Beri bonus bila tantangan pekan ini baru tuntas; kembalikan tantangan itu atau null. */
    public function syncWeeklyChallenge(): ?array
    {
        $weekKey = WeeklyChallenge::weekKey();
        if ($this->challengeCompletions()->where('week_key', $weekKey)->exists()) {
            return null;
        }

        $ch = WeeklyChallenge::current();
        if (($this->weeklyChallengeMetrics()[$ch['metric']] ?? 0) >= $ch['target']) {
            $this->challengeCompletions()->create([
                'week_key' => $weekKey,
                'challenge_key' => $ch['key'],
                'awarded_at' => now(),
            ]);
            $this->adjustments()->create(['amount' => $ch['bonus'], 'reason' => '🏆 Tantangan: '.$ch['title']]);

            return $ch;
        }

        return null;
    }

    /** Mood tercatat untuk sebuah tanggal (key) atau null. */
    public function moodFor(CarbonInterface $date): ?string
    {
        return $this->moods()->whereDate('date', $date->toDateString())->value('mood');
    }

    public function setMood(string $mood, CarbonInterface $date): void
    {
        // whereDate wajib: kolom cast `date` tersimpan sebagai "Y-m-d H:i:s".
        $existing = $this->moods()->whereDate('date', $date->toDateString())->first();

        if ($existing) {
            $existing->update(['mood' => $mood]);
        } else {
            $this->moods()->create(['date' => $date->toDateString(), 'mood' => $mood]);
        }
    }

    /** @return array<int, array{date: CarbonInterface, mood: ?string}> mood N hari terakhir (lama→baru). */
    public function recentMoods(int $days): array
    {
        $rows = $this->moods()
            ->whereDate('date', '>=', today()->subDays($days - 1)->toDateString())
            ->get()
            ->keyBy(fn (DailyMood $m) => $m->date->toDateString());

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = today()->subDays($i);
            $out[] = ['date' => $d, 'mood' => $rows->get($d->toDateString())?->mood];
        }

        return $out;
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

        // Jaga XP peliharaan = titik tertinggi poin tugas seumur hidup (tak menyusut).
        $lifetime = $this->totalPoints();
        if ($lifetime > (int) $this->pet_xp) {
            $this->update(['pet_xp' => $lifetime]);
        }

        return ['done' => $done, 'stats' => $this->statsForDate($date), 'photo_path' => $photoPath];
    }

    /** Progres peliharaan (tahap, level, emoji, XP menuju level berikut). */
    public function petProgress(): array
    {
        return Pet::progress($this->pet_type ?? 'naga', (int) $this->pet_xp);
    }

    /** Nilai metrik dasar untuk mengevaluasi lencana. */
    public function achievementMetrics(): array
    {
        $stats = $this->dailyStats(today()->subDays(179), today());
        $perfect = collect($stats)->filter(fn ($d) => $d['percent'] === 100)->count();

        return [
            'lifetime_tasks' => $this->completions()->count(),
            'lifetime_points' => $this->totalPoints(),
            'streak' => $this->streak(),
            'perfect_days' => $perfect,
            'pet_stage' => Pet::stageFor((int) $this->pet_xp),
        ];
    }

    /** Berikan lencana yang syaratnya baru terpenuhi; kembalikan yang BARU diraih. */
    public function syncAchievements(): \Illuminate\Support\Collection
    {
        $metrics = $this->achievementMetrics();
        $owned = $this->achievements()->pluck('key')->all();
        $newly = collect();

        foreach (Achievements::LIST as $key => $def) {
            if (in_array($key, $owned, true)) {
                continue;
            }
            if (($metrics[$def['metric']] ?? 0) >= $def['threshold']) {
                $this->achievements()->create(['key' => $key, 'earned_at' => now()]);
                $newly->push(['key' => $key] + $def);
            }
        }

        return $newly;
    }

    /** Daftar lencana untuk ditampilkan (yang diraih dulu, lalu terdekat). */
    public function achievementsProgress(): array
    {
        $metrics = $this->achievementMetrics();
        $earned = $this->achievements()->pluck('earned_at', 'key');

        $out = [];
        foreach (Achievements::LIST as $key => $def) {
            $value = min((int) ($metrics[$def['metric']] ?? 0), $def['threshold']);
            $out[] = [
                'key' => $key,
                'emoji' => $def['emoji'],
                'title' => $def['title'],
                'desc' => $def['desc'],
                'earned' => $earned->has($key),
                'earned_at' => $earned->get($key),
                'value' => $value,
                'threshold' => $def['threshold'],
                'percent' => (int) round($value / max($def['threshold'], 1) * 100),
            ];
        }

        usort($out, function ($a, $b) {
            if ($a['earned'] !== $b['earned']) {
                return $a['earned'] ? -1 : 1;
            }

            return $a['earned'] ? 0 : $b['percent'] <=> $a['percent'];
        });

        return $out;
    }

    /** Toggle tugas lalu kembalikan payload JSON siap pakai untuk front-end. */
    public function togglePayload(Task $task, CarbonInterface $date, ?UploadedFile $photo = null): array
    {
        $result = $this->toggleTask($task, $date, $photo);
        $stats = $result['stats'];
        $wc = $this->syncWeeklyChallenge();

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
            'pet' => $this->petProgress(),
            'achievements' => $this->syncAchievements()
                ->map(fn ($a) => ['emoji' => $a['emoji'], 'title' => $a['title']])->values(),
            'family_goal' => $this->familyGoalPayload(),
            'weekly_challenge' => $this->weeklyChallengeProgress(),
            'weekly_challenge_done' => $wc
                ? ['emoji' => $wc['emoji'], 'title' => $wc['title'], 'bonus' => $wc['bonus']] : null,
        ];
    }

    /** Ringkasan tujuan keluarga untuk front-end (null bila tak ada). */
    private function familyGoalPayload(): ?array
    {
        $goal = $this->familyGoal();
        if (! $goal) {
            return null;
        }

        $achievedNow = $goal->refreshAchieved();

        return [
            'title' => $goal->title,
            'emoji' => $goal->emoji,
            'percent' => $goal->percent(),
            'progress' => $goal->progress(),
            'target' => $goal->target,
            'achieved' => $goal->isAchieved(),
            'achieved_now' => $achievedNow,
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
