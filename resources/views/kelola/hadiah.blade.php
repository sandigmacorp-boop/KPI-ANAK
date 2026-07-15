@extends('layouts.app')

@section('title', 'Hadiah '.$child->name)
@section('page-title', $child->emoji.' Hadiah '.$child->name)
@section('back', route('children.index'))

@php($rewardEmojis = ['🎁', '🍦', '🍕', '🎮', '⚽', '🚲', '🧸', '🎨', '📚', '🎬', '🏖️', '💰', '👟', '🎂', '🍭', '🎡'])

@section('content')
    <p class="date-line">Poin {{ $child->name }}: 🏅 <b>{{ number_format($breakdown['balance'], 0, ',', '.') }}</b>
        · @include('partials.balance-breakdown', ['b' => $breakdown])
        · <a href="{{ route('points.index', $child) }}">⚖️ Atur poin</a>
    </p>

    @if ($pending->isNotEmpty())
        <section class="card reward-card reward-unlocked">
            <h3 class="card-title">🛎️ Perlu Diberikan ({{ $pending->count() }})</h3>
            @foreach ($pending as $redemption)
                <div class="reward-row">
                    <span class="reward-emoji" aria-hidden="true">{{ $redemption->emoji }}</span>
                    <span class="reward-body">
                        <span class="reward-title">{{ $redemption->title }}</span>
                        <span class="muted">🏅 {{ number_format($redemption->cost, 0, ',', '.') }} poin · ditukar {{ $redemption->created_at->translatedFormat('j M, H:i') }}</span>
                        <span class="row-actions">
                            <form method="post" action="{{ route('redemptions.deliver', $redemption) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm">✅ Sudah diberikan</button>
                            </form>
                            <form method="post" action="{{ route('redemptions.cancel', $redemption) }}"
                                  data-confirm="Batalkan penukaran '{{ $redemption->title }}'? {{ $redemption->cost }} poin akan dikembalikan.">
                                @csrf
                                <button class="btn btn-ghost btn-sm">↩️ Batalkan</button>
                            </form>
                        </span>
                    </span>
                </div>
            @endforeach
        </section>
    @endif

    <button type="button" class="btn btn-primary btn-block" data-dialog="dlg-hadiah-baru">➕ Tambah Hadiah</button>

    @if ($rewards->isEmpty())
        <div class="card empty">Katalog masih kosong. Tambahkan hadiah beserta harga poinnya, misalnya:<br><b>🍦 Es krim — 150 poin</b></div>
    @endif

    @foreach ($rewards as $reward)
        <section class="card reward-card {{ $reward->is_active ? '' : 'inactive' }}">
            <div class="reward-row">
                <span class="reward-emoji" aria-hidden="true">{{ $reward->emoji }}</span>
                <span class="reward-body">
                    <span class="reward-title">{{ $reward->title }}</span>
                    <span>
                        <span class="chip">🏅 {{ number_format($reward->cost, 0, ',', '.') }} poin</span>
                        @unless ($reward->is_active)
                            <span class="chip chip-muted">disembunyikan</span>
                        @endunless
                    </span>
                </span>
                <span class="task-actions">
                    <button type="button" class="iconbtn" data-dialog="dlg-hadiah-{{ $reward->id }}" aria-label="Edit hadiah">✏️</button>
                    <form method="post" action="{{ route('rewards.active', $reward) }}">
                        @csrf
                        <button class="iconbtn" title="{{ $reward->is_active ? 'Sembunyikan dari katalog anak' : 'Tampilkan di katalog anak' }}">
                            {{ $reward->is_active ? '⏸️' : '▶️' }}
                        </button>
                    </form>
                    <form method="post" action="{{ route('rewards.destroy', $reward) }}"
                          data-confirm="Hapus '{{ $reward->title }}' dari katalog? Riwayat penukaran tetap tersimpan.">
                        @csrf @method('DELETE')
                        <button class="iconbtn" aria-label="Hapus hadiah">🗑️</button>
                    </form>
                </span>
            </div>
        </section>

        <dialog id="dlg-hadiah-{{ $reward->id }}" class="sheet">
            <form method="post" action="{{ route('rewards.update', $reward) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="dlg-hadiah-{{ $reward->id }}">
                <div class="sheet-head">
                    <h3>Edit Hadiah</h3>
                    <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
                </div>
                @include('partials.reward-form-fields', ['reward' => $reward, 'rewardEmojis' => $rewardEmojis])
                <button class="btn btn-primary btn-block">Simpan</button>
            </form>
        </dialog>
    @endforeach

    @if ($history->isNotEmpty())
        <details class="numbers">
            <summary>Riwayat penukaran ({{ $history->count() }})</summary>
            <table class="table">
                <tbody>
                @foreach ($history as $redemption)
                    <tr>
                        <td>{{ $redemption->emoji }} {{ $redemption->title }}</td>
                        <td class="num">🏅 {{ number_format($redemption->cost, 0, ',', '.') }}</td>
                        <td>
                            @if ($redemption->canceled_at)
                                <span class="chip chip-muted">dibatalkan</span>
                            @else
                                <span class="chip chip-done">✅ {{ $redemption->delivered_at->translatedFormat('j M') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </details>
    @endif

    {{-- Dialog tambah hadiah --}}
    <dialog id="dlg-hadiah-baru" class="sheet">
        <form method="post" action="{{ route('rewards.store', $child) }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-hadiah-baru">
            <div class="sheet-head">
                <h3>Tambah Hadiah</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            @include('partials.reward-form-fields', ['reward' => null, 'rewardEmojis' => $rewardEmojis])
            <button class="btn btn-primary btn-block">Simpan</button>
        </form>
    </dialog>
@endsection
