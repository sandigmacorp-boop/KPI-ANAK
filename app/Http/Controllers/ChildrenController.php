<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Support\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ChildrenController extends Controller
{
    public function index(Request $request)
    {
        $children = $request->user()->children()
            ->withCount(['tasks as active_tasks_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('created_at')
            ->get();

        return view('kelola.anak', [
            'children' => $children,
            'activeGoal' => tap($request->user()->household?->activeGoal(), fn ($g) => $g?->refreshAchieved()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['household_id'] = $request->user()->household_id;
        $data['access_token'] = Str::random(40);

        Child::create($data);

        return redirect()->route('children.index')->with('ok', 'Anak berhasil ditambahkan.');
    }

    public function update(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);
        $child->update($this->validated($request));

        return redirect()->route('children.index')->with('ok', 'Data anak diperbarui.');
    }

    public function destroy(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);

        Storage::disk('public')->deleteDirectory('bukti/'.$child->id);
        $child->delete();

        return redirect()->route('children.index')->with('ok', 'Data anak dihapus.');
    }

    public function newToken(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);
        $child->update(['access_token' => Str::random(40)]);

        return redirect()->route('children.index')->with('ok', 'Link mode anak yang baru sudah dibuat. Link lama tidak berlaku lagi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'emoji' => ['required', 'string', 'max:16'],
            'color' => ['required', Rule::in(Child::COLORS)],
            'pet_type' => ['required', Rule::in(array_keys(Pet::SPECIES))],
        ]);
    }

    private function authorizeChild(Request $request, Child $child): void
    {
        abort_unless($request->user()->owns($child), 403);
    }
}
