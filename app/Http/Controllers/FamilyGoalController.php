<?php

namespace App\Http\Controllers;

use App\Models\FamilyGoal;
use Illuminate\Http\Request;

class FamilyGoalController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'target' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $household = $request->user()->household;
        $fields = [
            'title' => $data['title'],
            'emoji' => filled($data['emoji'] ?? null) ? $data['emoji'] : '🎯',
            'target' => $data['target'],
        ];

        // Sunting tujuan aktif di tempat (progres tetap), atau buat baru bila belum ada.
        if ($active = $household->activeGoal()) {
            $active->update($fields + ['achieved_at' => null]);
            $msg = 'Tujuan keluarga diperbarui.';
        } else {
            $household->goals()->create($fields);
            $msg = 'Tujuan keluarga dibuat! Semangat bersama! 🤝';
        }

        return redirect()->route('children.index')->with('ok', $msg);
    }

    public function claim(Request $request, FamilyGoal $goal)
    {
        $this->authorizeGoal($request, $goal);
        $goal->update(['claimed_at' => now()]);

        return redirect()->route('children.index')->with('ok', 'Tujuan ditandai selesai & dirayakan! 🎉');
    }

    public function destroy(Request $request, FamilyGoal $goal)
    {
        $this->authorizeGoal($request, $goal);
        $goal->delete();

        return redirect()->route('children.index')->with('ok', 'Tujuan keluarga dihapus.');
    }

    private function authorizeGoal(Request $request, FamilyGoal $goal): void
    {
        abort_unless($goal->household_id === $request->user()->household_id, 403);
    }
}
