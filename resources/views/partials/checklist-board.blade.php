{{-- $child, $day, $tasks (dikelompokkan per time_slot), $stats, $stars, $mode: 'admin'|'kid' --}}
@php($isAdmin = ($mode ?? 'admin') === 'admin')
@php($nowSlot = $day->isToday() ? \App\Models\Task::currentSlot() : null)
<div class="board" style="--child: {{ $child->color }}" data-date="{{ $day->toDateString() }}" data-mode="{{ $isAdmin ? 'admin' : 'kid' }}"
     data-slot="{{ $nowSlot }}" data-slot-until="{{ $nowSlot ? \App\Models\Task::SLOTS[$nowSlot]['until'] : '' }}">

    <section class="card progress-card">
        <div class="progress-top">
            <div class="progress-nums">
                <b class="progress-pct" id="pct">{{ $stats['percent'] === null ? '—' : $stats['percent'].'%' }}</b>
                <span class="muted">
                    <span id="done-count">{{ $stats['done_tasks'] }}</span>/{{ $stats['total_tasks'] }} tugas
                    · <span id="earned">{{ $stats['earned_points'] }}</span>/{{ $stats['total_points'] }} poin
                </span>
            </div>
            @include('partials.stars', ['stars' => $stars, 'id' => 'stars'])
        </div>
        <div class="bar" aria-hidden="true">
            <div class="bar-fill" id="bar-fill" style="width: {{ $stats['percent'] ?? 0 }}%"></div>
        </div>
        <div class="alldone" id="alldone"
             @if (! ($stats['total_tasks'] > 0 && $stats['done_tasks'] === $stats['total_tasks'])) hidden @endif>
            🎉 Hebat! Semua tugas selesai!
        </div>
    </section>

    @if ($stats['total_tasks'] === 0)
        <div class="card empty">🎈 Tidak ada tugas terjadwal pada hari ini.</div>
    @endif

    @foreach (\App\Models\Task::SLOTS as $slotKey => $slot)
        @if (isset($tasks[$slotKey]))
            <h3 class="slot-title">
                {{ $slot['emoji'] }} {{ $slot['label'] }}
                @if ($slotKey === $nowSlot)
                    <span class="chip chip-time">sekarang</span>
                @endif
            </h3>
            <div class="task-group">
                @foreach ($tasks[$slotKey] as $task)
                    @php($done = in_array($task->id, $stats['done_ids']))
                    @php($photoPath = $stats['photos'][$task->id] ?? null)
                    {{-- Kunci waktu cuma berlaku di mode anak untuk hari ini; orang tua tetap bisa koreksi kapan saja. --}}
                    @php($locked = ! $isAdmin && $day->isToday() && $task->isSlotOver())
                    <button type="button"
                            class="task-item {{ $done ? 'done' : '' }} {{ $locked ? 'locked' : '' }}"
                            data-toggle-url="{{ $isAdmin
                                ? route('checklist.toggle', [$child, $task])
                                : route('kid.toggle', [$child->access_token, $task]) }}"
                            data-photo="{{ $task->requires_photo ? 1 : 0 }}"
                            data-has-photo="{{ $photoPath ? 1 : 0 }}"
                            @if ($locked) disabled @endif>
                        <span class="task-emoji" aria-hidden="true">{{ $task->emoji }}</span>
                        <span class="task-body">
                            <span class="task-title">{{ $task->title }}</span>
                            <span class="task-sub">
                                {{ $task->points }} poin{{ $task->requires_photo ? ' · 📷 wajib foto' : '' }}
                                @if ($locked && ! $done)
                                    · ⏰ waktu habis
                                @endif
                            </span>
                        </span>
                        <img class="proof-thumb"
                             src="{{ $photoPath ? \App\Support\ProofPhoto::url($photoPath) : '' }}"
                             alt="Lihat bukti foto" title="Lihat bukti foto"
                             @unless ($photoPath) hidden @endunless>
                        <span class="task-check" aria-hidden="true">{{ $locked && ! $done ? '🔒' : '✓' }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    @endforeach

    <input type="file" id="proof-input" accept="image/*" capture="environment" hidden aria-hidden="true">
</div>
