<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\PointAdjustment;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function index(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);

        return view('kelola.poin', [
            'child' => $child,
            'breakdown' => $child->pointsBreakdown(),
            'adjustments' => $child->adjustments()->latest()->limit(50)->get(),
        ]);
    }

    public function store(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);

        $data = $request->validate([
            'kind' => ['required', 'in:bonus,penalty'],
            'points' => ['required', 'integer', 'min:1', 'max:100000'],
            'reason' => ['nullable', 'string', 'max:120'],
        ]);

        $amount = $data['kind'] === 'penalty' ? -$data['points'] : $data['points'];

        $child->adjustments()->create([
            'amount' => $amount,
            'reason' => $data['reason'] ?? null,
        ]);

        $label = $data['kind'] === 'penalty' ? 'Pengurangan' : 'Bonus';

        return redirect()->route('points.index', $child)
            ->with('ok', "$label {$data['points']} poin dicatat untuk {$child->name}.");
    }

    public function destroy(Request $request, PointAdjustment $adjustment)
    {
        abort_unless($request->user()->owns($adjustment->child), 403);

        $child = $adjustment->child;
        $adjustment->delete();

        return redirect()->route('points.index', $child)
            ->with('ok', 'Catatan poin dihapus (saldo disesuaikan kembali).');
    }

    private function authorizeChild(Request $request, Child $child): void
    {
        abort_unless($request->user()->owns($child), 403);
    }
}
