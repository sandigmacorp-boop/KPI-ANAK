{{-- $task: opsional (mode edit) · $taskEmojis: daftar pilihan ikon --}}
@php($isEdit = isset($task))
@php($resubmit = old('_form') !== null)
@include('partials.errors')

<label class="field">Nama tugas
    <input name="title" maxlength="80" required placeholder="contoh: Merapikan tempat tidur"
           value="{{ $isEdit ? $task->title : old('title') }}">
</label>

<div class="field">
    <span class="field-label">Ikon</span>
    <div class="picker">
        @php($current = $isEdit ? $task->emoji : old('emoji', '⭐'))
        @foreach (in_array($current, $taskEmojis) ? $taskEmojis : array_merge([$current], $taskEmojis) as $e)
            <label class="pick"><input type="radio" name="emoji" value="{{ $e }}" @checked($current === $e)><span>{{ $e }}</span></label>
        @endforeach
    </div>
</div>

<div class="grid-2">
    <label class="field">Poin
        <input type="number" name="points" min="1" max="100" required
               value="{{ $isEdit ? $task->points : old('points', 10) }}">
    </label>
    <label class="field">Waktu
        <select name="time_slot">
            @foreach (\App\Models\Task::SLOTS as $key => $slot)
                <option value="{{ $key }}" @selected(($isEdit ? $task->time_slot : old('time_slot', 'pagi')) === $key)>
                    {{ $slot['emoji'] }} {{ $slot['label'] }}
                </option>
            @endforeach
        </select>
    </label>
</div>

<label class="check-row">
    <input type="hidden" name="requires_photo" value="0">
    <input type="checkbox" name="requires_photo" value="1"
           @checked($isEdit ? $task->requires_photo : ($resubmit ? old('requires_photo') === '1' : true))>
    📷 Wajib foto bukti saat anak mencentang
</label>

@php($everyday = $isEdit ? empty($task->days) : ($resubmit ? old('everyday') === '1' : true))
<label class="check-row">
    <input type="checkbox" name="everyday" value="1" @checked($everyday)>
    Setiap hari
</label>

<div class="days-picker picker" @if($everyday) hidden @endif>
    @php($checkedDays = $isEdit ? ($task->days ?? []) : array_map('intval', (array) old('days', [])))
    @foreach (\App\Models\Task::DAY_NAMES as $num => $label)
        <label class="pick day"><input type="checkbox" name="days[]" value="{{ $num }}" @checked(in_array($num, $checkedDays))><span>{{ $label }}</span></label>
    @endforeach
</div>
