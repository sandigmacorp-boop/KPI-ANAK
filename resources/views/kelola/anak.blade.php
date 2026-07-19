@extends('layouts.app')

@section('title', 'Kelola Anak')
@section('page-title', 'Kelola Anak')

@php($emojis = ['😀', '🦁', '🐰', '🐱', '🐼', '🦊', '🐧', '🦄', '🐢', '🐬', '🐥', '🚀'])

@section('content')
    <section class="card">
        <h3 class="card-title">🤝 Tujuan Keluarga</h3>
        @if ($activeGoal)
            @include('partials.family-goal', ['goal' => $activeGoal])
            <div class="row-actions">
                @if ($activeGoal->isAchieved())
                    <form method="post" action="{{ route('goals.claim', $activeGoal) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm">🎉 Sudah dirayakan</button>
                    </form>
                @endif
                <button type="button" class="btn btn-ghost btn-sm" data-dialog="dlg-goal">✏️ Ubah</button>
                <form method="post" action="{{ route('goals.destroy', $activeGoal) }}" data-confirm="Hapus tujuan keluarga ini?">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">🗑️</button>
                </form>
            </div>
        @else
            <p class="muted">Buat target poin bersama — semua anak menyumbang untuk satu hadiah keluarga (tak mengurangi saldo pribadi).</p>
            <button type="button" class="btn btn-primary btn-block" data-dialog="dlg-goal">➕ Buat Tujuan Keluarga</button>
        @endif
    </section>

    <dialog id="dlg-goal" class="sheet">
        <form method="post" action="{{ route('goals.store') }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-goal">
            <div class="sheet-head">
                <h3>Tujuan Keluarga</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            @include('partials.errors')
            <label class="field">Nama tujuan
                <input name="title" maxlength="80" required placeholder="contoh: Jalan-jalan ke pantai"
                       value="{{ old('_form') === 'dlg-goal' ? old('title') : ($activeGoal->title ?? '') }}">
            </label>
            <div class="field">
                <span class="field-label">Ikon</span>
                <div class="picker">
                    @php($goalEmojis = ['🎯', '🏖️', '🍕', '🎢', '🎬', '🏕️', '🎮', '🍦', '🎪', '🚗'])
                    @php($goalEmoji = old('_form') === 'dlg-goal' ? old('emoji', '🎯') : ($activeGoal->emoji ?? '🎯'))
                    @foreach ($goalEmojis as $e)
                        <label class="pick"><input type="radio" name="emoji" value="{{ $e }}" @checked($goalEmoji === $e)><span>{{ $e }}</span></label>
                    @endforeach
                </div>
            </div>
            <label class="field">Target poin bersama
                <input type="number" name="target" min="1" max="1000000" required
                       value="{{ old('_form') === 'dlg-goal' ? old('target') : ($activeGoal->target ?? 1000) }}">
            </label>
            <p class="muted field-hint">Poin dari semua anak dijumlahkan menuju target ini. Saldo pribadi tiap anak tetap utuh.</p>
            <button class="btn btn-primary btn-block">Simpan</button>
        </form>
    </dialog>

    <button type="button" class="btn btn-primary btn-block" data-dialog="dlg-anak-baru">➕ Tambah Anak</button>

    @forelse ($children as $child)
        <section class="card child-card" style="--child: {{ $child->color }}">
            <div class="child-head">
                <div class="avatar" aria-hidden="true">{{ $child->emoji }}</div>
                <div class="child-meta">
                    <h2>{{ $child->name }}</h2>
                    <span class="muted">{{ $child->active_tasks_count }} tugas aktif</span>
                </div>
            </div>
            <div class="row-actions">
                <a class="btn btn-primary" href="{{ route('tasks.index', $child) }}">📋 Tugas</a>
                <a class="btn btn-ghost" href="{{ route('rewards.index', $child) }}">🎁 Hadiah</a>
                <a class="btn btn-ghost" href="{{ route('points.index', $child) }}">⚖️ Poin</a>
                <button type="button" class="btn btn-ghost" data-dialog="dlg-link-{{ $child->id }}">🔗 Link</button>
                <button type="button" class="btn btn-ghost" data-dialog="dlg-anak-{{ $child->id }}">✏️ Edit</button>
                <form method="post" action="{{ route('children.destroy', $child) }}"
                      data-confirm="Hapus {{ $child->name }}? Semua tugas dan riwayatnya ikut terhapus.">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" aria-label="Hapus {{ $child->name }}">🗑️</button>
                </form>
            </div>
        </section>

        {{-- Dialog edit anak --}}
        <dialog id="dlg-anak-{{ $child->id }}" class="sheet">
            <form method="post" action="{{ route('children.update', $child) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="dlg-anak-{{ $child->id }}">
                <div class="sheet-head">
                    <h3>Edit {{ $child->name }}</h3>
                    <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
                </div>
                @include('partials.errors')
                <label class="field">Nama
                    <input name="name" value="{{ $child->name }}" maxlength="50" required>
                </label>
                <div class="field">
                    <span class="field-label">Avatar</span>
                    <div class="picker">
                        @foreach (in_array($child->emoji, $emojis) ? $emojis : array_merge([$child->emoji], $emojis) as $e)
                            <label class="pick"><input type="radio" name="emoji" value="{{ $e }}" @checked($child->emoji === $e)><span>{{ $e }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <span class="field-label">Warna</span>
                    <div class="picker">
                        @foreach (\App\Models\Child::COLORS as $c)
                            <label class="pick swatch"><input type="radio" name="color" value="{{ $c }}" @checked($child->color === $c)><span style="background: {{ $c }}"></span></label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <span class="field-label">Peliharaan</span>
                    <div class="picker">
                        @foreach (\App\Support\Pet::SPECIES as $key => $sp)
                            <label class="pick pet-pick"><input type="radio" name="pet_type" value="{{ $key }}" @checked(($child->pet_type ?? 'naga') === $key)><span>{{ $sp['stages'][2] }}<small>{{ $sp['name'] }}</small></span></label>
                        @endforeach
                    </div>
                </div>
                <button class="btn btn-primary btn-block">Simpan</button>
            </form>
        </dialog>

        {{-- Dialog link mode anak --}}
        <dialog id="dlg-link-{{ $child->id }}" class="sheet">
            <div class="sheet-head">
                <h3>🔗 Link Mode Anak</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            <p class="muted">Buka link ini di HP/tablet {{ $child->name }} — tanpa perlu login, hanya bisa mencentang tugas hari ini.</p>
            <input class="link-input" readonly value="{{ $child->kidUrl() }}" onclick="this.select()">
            <div class="row-actions">
                <button type="button" class="btn btn-primary" data-copy="{{ $child->kidUrl() }}">📋 Salin</button>
                <a class="btn btn-ghost" href="{{ $child->kidUrl() }}" target="_blank" rel="noopener">Buka ↗</a>
            </div>
            <form method="post" action="{{ route('children.token', $child) }}"
                  data-confirm="Buat link baru? Link lama tidak bisa dipakai lagi.">
                @csrf
                <button class="linklike danger">♻️ Buat link baru (batalkan link lama)</button>
            </form>
        </dialog>
    @empty
        <div class="card empty">Belum ada data anak. Tekan <b>➕ Tambah Anak</b> di atas.</div>
    @endforelse

    {{-- Dialog tambah anak --}}
    <dialog id="dlg-anak-baru" class="sheet">
        <form method="post" action="{{ route('children.store') }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-anak-baru">
            <div class="sheet-head">
                <h3>Tambah Anak</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            @include('partials.errors')
            <label class="field">Nama
                <input name="name" value="{{ old('name') }}" maxlength="50" required placeholder="contoh: Kakak">
            </label>
            <div class="field">
                <span class="field-label">Avatar</span>
                <div class="picker">
                    @foreach ($emojis as $e)
                        <label class="pick"><input type="radio" name="emoji" value="{{ $e }}" @checked(old('emoji', '😀') === $e)><span>{{ $e }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <span class="field-label">Warna</span>
                <div class="picker">
                    @foreach (\App\Models\Child::COLORS as $c)
                        <label class="pick swatch"><input type="radio" name="color" value="{{ $c }}" @checked(old('color', \App\Models\Child::COLORS[0]) === $c)><span style="background: {{ $c }}"></span></label>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <span class="field-label">Peliharaan</span>
                <div class="picker">
                    @foreach (\App\Support\Pet::SPECIES as $key => $sp)
                        <label class="pick pet-pick"><input type="radio" name="pet_type" value="{{ $key }}" @checked(old('pet_type', 'naga') === $key)><span>{{ $sp['stages'][2] }}<small>{{ $sp['name'] }}</small></span></label>
                    @endforeach
                </div>
            </div>
            <button class="btn btn-primary btn-block">Simpan</button>
        </form>
    </dialog>
@endsection
