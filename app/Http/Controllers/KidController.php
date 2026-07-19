<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Reward;
use App\Models\Task;
use App\Support\Mood;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KidController extends Controller
{
    public function show(string $token)
    {
        $child = Child::where('access_token', $token)->firstOrFail();
        $today = today();
        $stats = $child->statsForDate($today);
        $slot = Task::currentSlot();
        $balance = $child->pointsBalance();

        return view('kid', [
            'child' => $child,
            'day' => $today,
            'tasks' => $child->tasksForDate($today)->groupBy('time_slot'),
            'stats' => $stats,
            'stars' => Child::starsFor($stats['percent']),
            'balance' => $balance,
            'pendingCount' => $child->redemptions()->pending()->count(),
            'affordableCount' => $child->rewards()->where('is_active', true)->where('cost', '<=', $balance)->count(),
            'slotNow' => $slot,
            'slotPending' => $child->pendingTasksInSlot($slot, $today),
        ]);
    }

    /** Dashboard performa milik si anak (tanpa login). */
    public function performa(string $token)
    {
        $child = Child::where('access_token', $token)->firstOrFail();
        $child->syncAchievements();

        $today = today();
        $stats = $child->statsForDate($today);
        $week = $child->dailyStats($today->copy()->subDays(6), $today);
        $activeDays = collect($week)->filter(fn ($d) => $d['percent'] !== null);
        $balance = $child->pointsBalance();

        return view('kid-performa', [
            'child' => $child,
            'day' => $today,
            'stats' => $stats,
            'stars' => Child::starsFor($stats['percent']),
            'streak' => $child->streak(),
            'balance' => $balance,
            'breakdown' => $child->pointsBreakdown(),
            'adjustments' => $child->adjustments()->latest()->limit(15)->get(),
            'week' => $week,
            'avg7' => $activeDays->isEmpty() ? null : (int) round($activeDays->avg('percent')),
            'catalog' => $child->rewards()->where('is_active', true)->orderBy('cost')->get(),
            'pendingRedemptions' => $child->redemptions()->pending()->orderBy('created_at')->get(),
            'history' => $child->redemptions()
                ->where(fn ($q) => $q->whereNotNull('delivered_at')->orWhereNotNull('canceled_at'))
                ->latest()->limit(10)->get(),
        ]);
    }

    /** Anak menukar poin dengan hadiah dari katalog. */
    public function redeem(string $token, Reward $reward)
    {
        $child = Child::where('access_token', $token)->firstOrFail();
        abort_unless($reward->child_id === $child->id && $reward->is_active, 404);

        if (! $child->canAfford($reward)) {
            return redirect()->route('kid.performa', $token)
                ->with('err', 'Poinmu belum cukup untuk hadiah ini. Semangat kumpulkan lagi! 💪');
        }

        $child->redeem($reward);

        return redirect()->route('kid.performa', $token)
            ->with('ok', "🎉 {$reward->emoji} {$reward->title} berhasil ditukar! Tunjukkan ke Ayah/Bunda untuk menerimanya.");
    }

    /** Data pengingat (untuk notifikasi perangkat & auto-refresh). */
    public function reminder(string $token)
    {
        $child = Child::where('access_token', $token)->firstOrFail();
        $slot = Task::currentSlot();
        $pending = $child->pendingTasksInSlot($slot, today());

        return response()->json([
            'slot' => $slot,
            'slot_label' => $slot ? Task::SLOTS[$slot]['label'] : null,
            'slot_until' => $slot ? Task::SLOTS[$slot]['until'] : null,
            'pending' => $pending->count(),
            'titles' => $pending->take(5)->map(fn (Task $t) => $t->emoji.' '.$t->title)->values(),
            'balance' => $child->pointsBalance(),
        ]);
    }

    /** Anak mencatat perasaannya hari ini. */
    public function setMood(Request $request, string $token)
    {
        $child = Child::where('access_token', $token)->firstOrFail();
        $data = $request->validate(['mood' => ['required', Rule::in(array_keys(Mood::MOODS))]]);
        $child->setMood($data['mood'], today());

        return response()->json(['ok' => true, 'mood' => $data['mood']]);
    }

    public function toggle(Request $request, string $token, Task $task)
    {
        $child = Child::where('access_token', $token)->firstOrFail();
        abort_unless($task->child_id === $child->id, 404);

        $request->validate(['photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);

        // Mode anak hanya boleh mencentang hari ini, dan tugas berfoto wajib pakai bukti.
        $isChecking = ! $child->completions()
            ->where('task_id', $task->id)
            ->whereDate('date', today()->toDateString())
            ->exists();

        if ($isChecking && $task->requires_photo && ! $request->hasFile('photo')) {
            return response()->json(['message' => 'Sertakan foto bukti dulu ya! 📷'], 422);
        }

        return response()->json($child->togglePayload($task, today(), $request->file('photo')));
    }

    /** Manifest PWA khusus per anak, agar bisa di-install di perangkat si anak. */
    public function manifest(string $token)
    {
        $child = Child::where('access_token', $token)->firstOrFail();

        return response()->json([
            'name' => 'Tugas '.$child->name,
            'short_name' => $child->name,
            'lang' => 'id',
            'start_url' => route('kid.show', $child->access_token),
            'scope' => url('/c/'),
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#FAF3E8',
            'theme_color' => $child->color,
            'icons' => [
                ['src' => asset('icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset('icons/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => asset('icons/icon-512-maskable.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
