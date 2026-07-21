@extends('layouts.app')

@section('title', 'Tantangan Tim')
@section('page-title', '🤝 Tantangan Tim')
@section('back', route('children.index'))

@php($teamEmojis = ['🤝', '🧹', '🏡', '🌳', '🚗', '🍳', '📦', '🧺', '🎨', '🛠️'])

@section('content')
    <p class="muted">Misi bersama seluruh anak: satu laporan foto mewakili tim, dan bila Anda setujui, <b>semua anak</b> mendapat poin yang sama.</p>

    <button type="button" class="btn btn-primary btn-block" data-dialog="dlg-team-baru">➕ Buat Tantangan Tim</button>

    @if ($active->isEmpty())
        <div class="card empty">Belum ada tantangan tim aktif.</div>
    @endif

    @foreach ($active as $ch)
        <section class="card team-card {{ $ch->isPending() ? 'team-pending' : '' }}">
            <div class="goal-head">
                <span class="goal-emoji" aria-hidden="true">{{ $ch->emoji }}</span>
                <div class="goal-body">
                    <b class="goal-title">{{ $ch->title }}</b>
                    @if ($ch->description)
                        <span class="muted">{{ $ch->description }}</span>
                    @endif
                    <span class="chip">🏅 {{ $ch->points }} poin / anak</span>
                </div>
            </div>

            @if ($ch->isOpen())
                <p class="muted team-status">⏳ Menunggu tim mengirim laporan.</p>
                <div class="row-actions">
                    <button type="button" class="btn btn-ghost btn-sm" data-dialog="dlg-team-{{ $ch->id }}">✏️ Ubah</button>
                    <form method="post" action="{{ route('team.destroy', $ch) }}" data-confirm="Hapus tantangan '{{ $ch->title }}'?">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">🗑️ Hapus</button>
                    </form>
                </div>
            @elseif ($ch->isPending())
                @php($sub = $ch->pendingSubmission())
                <div class="team-report">
                    <p class="team-status">🛎️ Laporan dari <b>{{ $sub->child->emoji }} {{ $sub->child->name }}</b> · {{ $sub->created_at->translatedFormat('j M, H:i') }}</p>
                    @if ($sub->note)
                        <p class="team-note">"{{ $sub->note }}"</p>
                    @endif
                    <div class="team-photos">
                        @foreach ($sub->photos as $photo)
                            <img class="proof-thumb team-thumb" src="{{ $photo->url() }}" alt="Foto laporan tim">
                        @endforeach
                    </div>
                    <div class="row-actions">
                        <form method="post" action="{{ route('team.approve', $sub) }}"
                              data-confirm="Setujui? {{ $ch->points }} poin akan diberikan ke setiap anak.">
                            @csrf
                            <button class="btn btn-primary btn-sm">✅ Setujui (+{{ $ch->points }} poin/anak)</button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" data-dialog="dlg-reject-{{ $sub->id }}">✗ Tolak</button>
                    </div>
                </div>

                <dialog id="dlg-reject-{{ $sub->id }}" class="sheet">
                    <form method="post" action="{{ route('team.reject', $sub) }}">
                        @csrf
                        <div class="sheet-head">
                            <h3>Tolak Laporan</h3>
                            <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
                        </div>
                        <label class="field">Alasan <small class="muted">(dilihat tim, opsional)</small>
                            <input name="review_note" maxlength="200" placeholder="contoh: foto kurang jelas, coba lagi ya">
                        </label>
                        <button class="btn btn-danger btn-block">Tolak Laporan</button>
                    </form>
                </dialog>
            @endif
        </section>

        <dialog id="dlg-team-{{ $ch->id }}" class="sheet">
            <form method="post" action="{{ route('team.update', $ch) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="dlg-team-{{ $ch->id }}">
                <div class="sheet-head">
                    <h3>Ubah Tantangan Tim</h3>
                    <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
                </div>
                @include('partials.team-form-fields', ['challenge' => $ch, 'teamEmojis' => $teamEmojis])
                <button class="btn btn-primary btn-block">Simpan</button>
            </form>
        </dialog>
    @endforeach

    @if ($completed->isNotEmpty())
        <details class="numbers">
            <summary>Tantangan selesai ({{ $completed->count() }})</summary>
            @foreach ($completed as $ch)
                @php($sub = $ch->submissions->firstWhere('status', 'approved'))
                <div class="reward-row">
                    <span class="reward-emoji" aria-hidden="true">{{ $ch->emoji }}</span>
                    <span class="reward-body">
                        <span class="reward-title">{{ $ch->title }}</span>
                        <span class="muted">✅ {{ $ch->completed_at?->translatedFormat('j M Y') }} · +{{ $ch->points }} poin/anak
                            @if ($sub) · laporan oleh {{ $sub->child->name }} @endif
                        </span>
                    </span>
                </div>
            @endforeach
        </details>
    @endif

    <dialog id="dlg-team-baru" class="sheet">
        <form method="post" action="{{ route('team.store') }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-team-baru">
            <div class="sheet-head">
                <h3>Buat Tantangan Tim</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            @include('partials.team-form-fields', ['challenge' => null, 'teamEmojis' => $teamEmojis])
            <button class="btn btn-primary btn-block">Simpan</button>
        </form>
    </dialog>
@endsection
