@extends('layouts.app')

@section('title', 'Poin '.$child->name)
@section('page-title', $child->emoji.' Poin '.$child->name)
@section('back', route('children.index'))

@section('content')
    <section class="card balance-card" style="--child: {{ $child->color }}">
        <div class="balance-line">
            <span class="balance-num">🏅 {{ number_format($breakdown['balance'], 0, ',', '.') }}</span>
            <span class="stat-cap">Saldo poin {{ $child->name }}</span>
        </div>
        <p class="balance-detail">@include('partials.balance-breakdown', ['b' => $breakdown])</p>
    </section>

    <div class="row-actions">
        <button type="button" class="btn btn-primary" data-dialog="dlg-bonus">🎉 Beri Bonus</button>
        <button type="button" class="btn btn-danger" data-dialog="dlg-penalty">⚠️ Kurangi Poin</button>
    </div>

    <h3 class="slot-title">Riwayat Penyesuaian</h3>
    @if ($adjustments->isEmpty())
        <div class="card empty">Belum ada bonus atau pengurangan poin.</div>
    @else
        <div class="task-group">
            @foreach ($adjustments as $adj)
                <div class="task-item static">
                    <span class="task-emoji" aria-hidden="true">{{ $adj->emoji() }}</span>
                    <span class="task-body">
                        <span class="task-title">{{ $adj->reason ?: ($adj->isBonus() ? 'Bonus' : 'Pengurangan poin') }}</span>
                        <span class="task-sub">{{ $adj->created_at->translatedFormat('l, j M Y · H:i') }}</span>
                    </span>
                    <span class="adj-amount {{ $adj->isBonus() ? 'plus' : 'minus' }}">{{ $adj->signed() }}</span>
                    <form method="post" action="{{ route('points.destroy', $adj) }}"
                          data-confirm="Hapus catatan ini? Saldo poin {{ $child->name }} akan disesuaikan kembali.">
                        @csrf @method('DELETE')
                        <button class="iconbtn" aria-label="Hapus catatan">🗑️</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Dialog bonus --}}
    <dialog id="dlg-bonus" class="sheet">
        <form method="post" action="{{ route('points.store', $child) }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-bonus">
            <input type="hidden" name="kind" value="bonus">
            <div class="sheet-head">
                <h3>🎉 Beri Bonus Poin</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            @include('partials.errors')
            <label class="field">Jumlah poin
                <input type="number" name="points" min="1" max="100000" required placeholder="contoh: 20"
                       value="{{ old('_form') === 'dlg-bonus' ? old('points') : '' }}">
            </label>
            <label class="field">Alasan <small class="muted">(dilihat anak)</small>
                <input name="reason" maxlength="120" placeholder="contoh: bantu cuci piring tanpa disuruh"
                       value="{{ old('_form') === 'dlg-bonus' ? old('reason') : '' }}">
            </label>
            <button class="btn btn-primary btn-block">Simpan Bonus</button>
        </form>
    </dialog>

    {{-- Dialog pengurangan --}}
    <dialog id="dlg-penalty" class="sheet">
        <form method="post" action="{{ route('points.store', $child) }}">
            @csrf
            <input type="hidden" name="_form" value="dlg-penalty">
            <input type="hidden" name="kind" value="penalty">
            <div class="sheet-head">
                <h3>⚠️ Kurangi Poin</h3>
                <button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>
            </div>
            @include('partials.errors')
            <label class="field">Jumlah poin dikurangi
                <input type="number" name="points" min="1" max="100000" required placeholder="contoh: 10"
                       value="{{ old('_form') === 'dlg-penalty' ? old('points') : '' }}">
            </label>
            <label class="field">Alasan <small class="muted">(dilihat anak)</small>
                <input name="reason" maxlength="120" placeholder="contoh: tidak merapikan mainan"
                       value="{{ old('_form') === 'dlg-penalty' ? old('reason') : '' }}">
            </label>
            <button class="btn btn-danger btn-block">Simpan Pengurangan</button>
        </form>
    </dialog>
@endsection
