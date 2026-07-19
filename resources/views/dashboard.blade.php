@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', config('app.name'))

@section('content')
    <p class="date-line">📅 {{ $today->translatedFormat('l, j F Y') }}</p>

    @forelse ($children as $row)
        @php($child = $row['child'])
        @php($stats = $row['stats'])
        @php($pet = $child->petProgress())
        <section class="card child-card" style="--child: {{ $child->color }}">
            <div class="child-head">
                <div class="avatar" aria-hidden="true">{{ $child->emoji }}</div>
                <div class="child-meta">
                    <h2>{{ $child->name }}</h2>
                    <div class="chips">
                        <span class="chip" title="{{ $pet['species'] }} {{ $pet['stage_name'] }}">{{ $pet['emoji'] }} Lv {{ $pet['level'] }}</span>
                        <span class="chip" title="Hari beruntun mencapai target">🔥 {{ $row['streak'] }} hari</span>
                        <span class="chip">🏅 {{ $stats['earned_points'] }}/{{ $stats['total_points'] }} poin</span>
                        @if ($row['pending_redemptions'] > 0)
                            <a class="chip chip-gift" href="{{ route('rewards.index', $child) }}">🎁 {{ $row['pending_redemptions'] }} hadiah perlu diberikan</a>
                        @endif
                        @if ($slotNow && $row['slot_pending'] > 0)
                            <span class="chip chip-time">⏰ {{ $row['slot_pending'] }} tugas {{ \App\Models\Task::SLOTS[$slotNow]['label'] }} belum</span>
                        @endif
                    </div>
                    @include('partials.stars', ['stars' => $row['stars']])
                </div>
                @include('partials.ring', ['percent' => $stats['percent']])
            </div>
            <div class="row-actions">
                <a class="btn btn-primary" href="{{ route('checklist', $child) }}">📝 Checklist</a>
                <a class="btn btn-ghost" href="{{ route('report', $child) }}">📊 Laporan</a>
            </div>
            <div class="kid-link">
                🔗 Mode anak:
                <button type="button" class="linklike" data-copy="{{ $child->kidUrl() }}">Salin link</button>
                <a href="{{ $child->kidUrl() }}" target="_blank" rel="noopener">Buka ↗</a>
            </div>
        </section>
    @empty
        <div class="card empty">
            <p>👋 Selamat datang! Belum ada data anak.</p>
            <a class="btn btn-primary" href="{{ route('children.index') }}">➕ Tambah Anak</a>
        </div>
    @endforelse
@endsection
