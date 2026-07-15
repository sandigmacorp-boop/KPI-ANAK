@extends('layouts.app')

@section('title', 'Tugas '.$child->name)
@section('page-title', $child->emoji.' Tugas '.$child->name)
@section('back', route('children.index'))

@php($taskEmojis = ['⭐', '🛏️', '🪥', '🍳', '🧸', '😴', '📚', '📖', '🤝', '🎒', '🌙', '🧹', '👟', '🥗', '🙏', '🐶'])

@section('content')
    <button type="button" class="btn btn-primary btn-block" data-dialog="dlg-tugas-baru">➕ Tambah Tugas</button>

    @if ($tasks->isEmpty())
        <div class="card empty">Belum ada tugas. Tekan <b>➕ Tambah Tugas</b> di atas.</div>
    @endif

    @foreach (\App\Models\Task::SLOTS as $slotKey => $slot)
        @if (isset($tasks[$slotKey]))
            <h3 class="slot-title">{{ $slot['emoji'] }} {{ $slot['label'] }}</h3>
            <div class="task-group">
                @foreach ($tasks[$slotKey] as $task)
                    <div class="task-item static {{ $task->is_active ? '' : 'inactive' }}">
                        <span class="task-emoji" aria-hidden="true">{{ $task->emoji }}</span>
                        <span class="task-body">
                            <span class="task-title">{{ $task->title }}</span>
                            <span class="task-sub">
                                {{ $task->points }} poin · {{ $task->daysLabel() }}
                                @unless ($task->is_active) · <b>nonaktif</b> @endunless
                            </span>
                        </span>
                        <span class="task-actions">
                            <button type="button" class="iconbtn" data-dialog="dlg-tugas-{{ $task->id }}" aria-label="Edit tugas">✏️</button>
                            <form method="post" action="{{ route('tasks.active', $task) }}">
                                @csrf
                                <button class="iconbtn" aria-label="{{ $task->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        title="{{ $task->is_active ? 'Nonaktifkan (liburkan) tugas' : 'Aktifkan kembali' }}">
                                    {{ $task->is_active ? '⏸️' : '▶️' }}
                                </button>
                            </form>
                            <form method="post" action="{{ route('tasks.destroy', $task) }}"
                                  data-confirm="Hapus tugas '{{ $task->title }}' beserta seluruh riwayatnya?">
                                @csrf @method('DELETE')
                                <button class="iconbtn" aria-label="Hapus tugas">🗑️</button>
                            </form>
                        </span>
                    </div>

                    <dialog id="dlg-tugas-{{ $task->id }}" class="sheet">
                        <form method="post" action="{{ route('tasks.update', $task) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="_form" value="dlg-tugas-{{ $task->id }}">
                            <div class="sheet-head">
                                <h3>Edit Tugas</h3>
                                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
                            </div>
                            @include('partials.task-form-fields', ['task' => $task, 'taskEmojis' => $taskEmojis])
                            <button class="btn btn-primary btn-block">Simpan</button>
                        </form>
                    </dialog>
                @endforeach
            </div>
        @endif
    @endforeach

    {{-- Dialog tambah tugas --}}
    <dialog id="dlg-tugas-baru" class="sheet">
        <form method="post" action="{{ route('tasks.store', $child) }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-tugas-baru">
            <div class="sheet-head">
                <h3>Tambah Tugas</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            {{-- 'task' => null wajib: menimpa $task yang bocor dari @foreach di atas --}}
            @include('partials.task-form-fields', ['task' => null, 'taskEmojis' => $taskEmojis])
            <button class="btn btn-primary btn-block">Simpan</button>
        </form>
    </dialog>
@endsection
