<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Task;
use App\Support\ProofPhoto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TasksController extends Controller
{
    public function index(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);

        $tasks = $child->tasks()->get()
            ->sortBy([
                fn (Task $a, Task $b) => $a->slotOrder() <=> $b->slotOrder(),
                fn (Task $a, Task $b) => $a->id <=> $b->id,
            ])
            ->groupBy('time_slot');

        return view('kelola.tugas', ['child' => $child, 'tasks' => $tasks]);
    }

    public function store(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);

        $child->tasks()->create($this->validated($request));

        return redirect()->route('tasks.index', $child)->with('ok', 'Tugas berhasil ditambahkan.');
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->update($this->validated($request));

        return redirect()->route('tasks.index', $task->child)->with('ok', 'Tugas diperbarui.');
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $child = $task->child;

        // Hapus file foto bukti sebelum riwayatnya ikut terhapus (cascade).
        $task->completions()->whereNotNull('photo_path')->pluck('photo_path')
            ->each(fn ($path) => ProofPhoto::delete($path));

        $task->delete();

        return redirect()->route('tasks.index', $child)->with('ok', 'Tugas dihapus beserta riwayatnya.');
    }

    public function toggleActive(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->update(['is_active' => ! $task->is_active]);

        return redirect()->route('tasks.index', $task->child)
            ->with('ok', $task->is_active ? 'Tugas diaktifkan kembali.' : 'Tugas dinonaktifkan (riwayat tetap tersimpan).');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'time_slot' => ['required', Rule::in(array_keys(Task::SLOTS))],
            'requires_photo' => ['nullable', 'boolean'],
            'everyday' => ['nullable', 'boolean'],
            'days' => ['required_unless:everyday,1', 'nullable', 'array', 'min:1'],
            'days.*' => ['integer', 'between:1,7'],
        ], [
            'days.required_unless' => 'Pilih minimal satu hari, atau centang "Setiap hari".',
        ]);

        return [
            'title' => $data['title'],
            'emoji' => filled($data['emoji'] ?? null) ? $data['emoji'] : '⭐',
            'points' => $data['points'],
            'time_slot' => $data['time_slot'],
            'requires_photo' => $request->boolean('requires_photo'),
            'days' => $request->boolean('everyday') ? null : array_map('intval', $data['days'] ?? []),
        ];
    }

    private function authorizeChild(Request $request, Child $child): void
    {
        abort_unless($request->user()->owns($child), 403);
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($request->user()->owns($task->child), 403);
    }
}
