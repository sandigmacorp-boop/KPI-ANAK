<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Task;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function show(Request $request, Child $child)
    {
        abort_unless($request->user()->owns($child), 403);

        $today = today();
        $from30 = $today->copy()->subDays(29);

        $week = $child->dailyStats($today->copy()->subDays(6), $today);
        $month = $child->dailyStats($from30, $today);

        $activeDays = collect($month)->filter(fn ($d) => $d['percent'] !== null);

        // Rekap per tugas selama 30 hari terakhir.
        $tasks = $child->tasks()->where('is_active', true)->get()
            ->sortBy([
                fn (Task $a, Task $b) => $a->slotOrder() <=> $b->slotOrder(),
                fn (Task $a, Task $b) => $a->id <=> $b->id,
            ]);

        $completions = $child->completions()
            ->whereDate('date', '>=', $from30->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->get()
            ->groupBy('task_id');

        $rateStart = $child->created_at->copy()->startOfDay()->max($from30);
        $period = iterator_to_array(CarbonPeriod::create($rateStart, $today));

        $taskRows = $tasks->map(function (Task $task) use ($completions, $period) {
            $scheduled = collect($period)->filter(fn ($day) => $task->isScheduledOn($day))->count();
            $done = min(($completions[$task->id] ?? collect())->count(), max($scheduled, 0));

            return [
                'task' => $task,
                'scheduled' => $scheduled,
                'done' => $done,
                'rate' => $scheduled > 0 ? (int) round($done / $scheduled * 100) : null,
            ];
        });

        return view('laporan', [
            'child' => $child,
            'week' => $week,
            'avg30' => $activeDays->isEmpty() ? null : (int) round($activeDays->avg('percent')),
            'earned30' => collect($month)->sum('earned_points'),
            'threeStarDays' => $activeDays->filter(fn ($d) => $d['percent'] >= Child::STAR_3)->count(),
            'streak' => $child->streak(),
            'taskRows' => $taskRows,
            'breakdown' => $child->pointsBreakdown(),
            'pendingRedemptions' => $child->redemptions()->pending()->orderBy('created_at')->get(),
            'adjustments' => $child->adjustments()->latest()->limit(10)->get(),
        ]);
    }
}
