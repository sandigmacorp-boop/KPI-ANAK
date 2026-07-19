@extends('layouts.app')

@section('title', 'Kelola Anak')
@section('page-title', 'Kelola Anak')

@php($emojis = ['😀', '🦁', '🐰', '🐱', '🐼', '🦊', '🐧', '🦄', '🐢', '🐬', '🐥', '🚀'])

@section('content')
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
