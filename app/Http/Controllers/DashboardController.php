<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = today();
        $slot = Task::currentSlot();

        $children = $request->user()->children()->orderBy('created_at')->get()
            ->map(function (Child $child) use ($today, $slot) {
                $stats = $child->statsForDate($today);

                return [
                    'child' => $child,
                    'stats' => $stats,
                    'stars' => Child::starsFor($stats['percent']),
                    'streak' => $child->streak(),
                    'pending_redemptions' => $child->redemptions()->pending()->count(),
                    'slot_pending' => $child->pendingTasksInSlot($slot, $today)->count(),
                ];
            });

        return view('dashboard', [
            'children' => $children,
            'today' => $today,
            'slotNow' => $slot,
        ]);
    }
}
