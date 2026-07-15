{{-- $reward: opsional (mode edit) · $rewardEmojis: pilihan ikon --}}
@php($isEdit = isset($reward) && $reward !== null)
@include('partials.errors')

<label class="field">Nama hadiah
    <input name="title" maxlength="80" required placeholder="contoh: Es krim spesial"
           value="{{ $isEdit ? $reward->title : old('title') }}">
</label>

<div class="field">
    <span class="field-label">Ikon</span>
    <div class="picker">
        @php($current = $isEdit ? $reward->emoji : old('emoji', '🎁'))
        @foreach (in_array($current, $rewardEmojis) ? $rewardEmojis : array_merge([$current], $rewardEmojis) as $e)
            <label class="pick"><input type="radio" name="emoji" value="{{ $e }}" @checked($current === $e)><span>{{ $e }}</span></label>
        @endforeach
    </div>
</div>

<label class="field">Harga (poin)
    <input type="number" name="cost" min="1" max="1000000" required
           value="{{ $isEdit ? $reward->cost : old('cost', 100) }}">
</label>
<p class="muted field-hint">🏅 Anak menukar poinnya dengan hadiah ini. Sebagai gambaran: satu hari sempurna ≈ 100 poin.</p>
