{{-- $challenge: opsional (mode edit) · $teamEmojis: pilihan ikon --}}
@php($isEdit = isset($challenge) && $challenge !== null)
@php($resub = old('_form') === ($isEdit ? 'dlg-team-'.$challenge->id : 'dlg-team-baru'))
@include('partials.errors')

<label class="field">Nama tantangan
    <input name="title" maxlength="80" required placeholder="contoh: Bersihkan Garasi Bersama"
           value="{{ $resub ? old('title') : ($isEdit ? $challenge->title : '') }}">
</label>
<label class="field">Instruksi <small class="muted">(opsional)</small>
    <input name="description" maxlength="200" placeholder="contoh: rapikan & sapu garasi, foto sebelum-sesudah"
           value="{{ $resub ? old('description') : ($isEdit ? $challenge->description : '') }}">
</label>
<div class="field">
    <span class="field-label">Ikon</span>
    <div class="picker">
        @php($current = $resub ? old('emoji', '🤝') : ($isEdit ? $challenge->emoji : '🤝'))
        @foreach (in_array($current, $teamEmojis) ? $teamEmojis : array_merge([$current], $teamEmojis) as $e)
            <label class="pick"><input type="radio" name="emoji" value="{{ $e }}" @checked($current === $e)><span>{{ $e }}</span></label>
        @endforeach
    </div>
</div>
<label class="field">Poin per anak jika disetujui
    <input type="number" name="points" min="1" max="10000" required
           value="{{ $resub ? old('points') : ($isEdit ? $challenge->points : 50) }}">
</label>
<p class="muted field-hint">Bila laporan tim disetujui, <b>setiap anak</b> di keluarga mendapat poin sebanyak ini.</p>
