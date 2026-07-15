<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Task;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChecklistController extends Controller
{
    public function show(Request $request, Child $child, ?string $date = null)
    {
        $this->authorizeChild($request, $child);

        $day = $this->resolveDate($date);
        $stats = $child->statsForDate($day);

        return view('checklist', [
            'child' => $child,
            'day' => $day,
            'tasks' => $child->tasksForDate($day)->groupBy('time_slot'),
            'stats' => $stats,
            'stars' => Child::starsFor($stats['percent']),
            'prevDate' => $day->copy()->subDay()->toDateString(),
            'nextDate' => $day->isToday() ? null : $day->copy()->addDay()->toDateString(),
        ]);
    }

    public function toggle(Request $request, Child $child, Task $task)
    {
        $this->authorizeChild($request, $child);
        abort_unless($task->child_id === $child->id, 404);

        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $day = $this->resolveDate($data['date'] ?? null);

        // Orang tua boleh mencentang tanpa foto (mereka sendiri verifikatornya).
        return response()->json($child->togglePayload($task, $day, $request->file('photo')));
    }

    private function authorizeChild(Request $request, Child $child): void
    {
        abort_unless($child->user_id === $request->user()->id, 403);
    }

    private function resolveDate(?string $date): Carbon
    {
        if ($date === null) {
            return today();
        }

        try {
            $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (InvalidFormatException) {
            abort(404);
        }

        abort_if($day->greaterThan(today()), 404);

        return $day;
    }
}
