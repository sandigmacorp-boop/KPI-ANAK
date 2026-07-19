@extends('layouts.app')

@section('title', 'Laporan '.$child->name)
@section('page-title', $child->emoji.' Laporan '.$child->name)
@section('back', route('home'))

@section('content')
    <div style="--child: {{ $child->color }}">

        <div class="stat-grid">
            <div class="card stat">
                <span class="stat-num">{{ $avg30 === null ? '—' : $avg30.'%' }}</span>
                <span class="stat-cap">Rata-rata KPI 30 hari</span>
            </div>
            <div class="card stat">
                <span class="stat-num">🏅 {{ number_format($earned30, 0, ',', '.') }}</span>
                <span class="stat-cap">Poin 30 hari</span>
            </div>
            <div class="card stat">
                <span class="stat-num">⭐ {{ $threeStarDays }}</span>
                <span class="stat-cap">Hari bintang 3</span>
            </div>
            <div class="card stat">
                <span class="stat-num">🔥 {{ $streak }}</span>
                <span class="stat-cap">Streak (hari)</span>
            </div>
        </div>

        <section class="card">
            <h3 class="card-title">KPI Harian — 7 Hari Terakhir <small class="muted">(%)</small></h3>
            @include('partials.week-bars')
        </section>

        <section class="card">
            <h3 class="card-title">🎁 Poin & Hadiah
                <small><a href="{{ route('points.index', $child) }}">⚖️ Atur poin</a> · <a href="{{ route('rewards.index', $child) }}">🎁 Hadiah</a></small>
            </h3>
            <p>Saldo poin: <b>🏅 {{ number_format($breakdown['balance'], 0, ',', '.') }}</b><br>
                @include('partials.balance-breakdown', ['b' => $breakdown])
            </p>

            @if ($pendingRedemptions->isNotEmpty())
                @foreach ($pendingRedemptions as $redemption)
                    <div class="reward-row">
                        <span class="reward-emoji" aria-hidden="true">{{ $redemption->emoji }}</span>
                        <span class="reward-body">
                            <span class="reward-title">{{ $redemption->title }}</span>
                            <span class="muted">🏅 {{ number_format($redemption->cost, 0, ',', '.') }} poin · ditukar {{ $redemption->created_at->translatedFormat('j M, H:i') }}</span>
                        </span>
                        <span class="chip chip-gift">perlu diberikan</span>
                    </div>
                @endforeach
            @endif

            @if ($adjustments->isNotEmpty())
                <table class="table" style="margin-top: 10px;">
                    <tbody>
                    @foreach ($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->emoji() }} {{ $adj->reason ?: ($adj->isBonus() ? 'Bonus' : 'Pengurangan') }}</td>
                            <td class="num"><b class="adj-amount {{ $adj->isBonus() ? 'plus' : 'minus' }}">{{ $adj->signed() }}</b></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="card">
            <h3 class="card-title">Ketuntasan per Tugas <small class="muted">(30 hari)</small></h3>
            @if ($taskRows->isEmpty())
                <p class="muted">Belum ada tugas aktif.</p>
            @else
                <table class="table task-rate">
                    <tbody>
                    @foreach ($taskRows as $row)
                        <tr>
                            <td>{{ $row['task']->emoji }} {{ $row['task']->title }}</td>
                            <td class="num">{{ $row['done'] }}/{{ $row['scheduled'] }}</td>
                            <td class="num"><b>{{ $row['rate'] === null ? '—' : $row['rate'].'%' }}</b></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        @include('partials.achievements')

        <p class="legend muted">Bintang harian: ⭐⭐⭐ ≥ {{ \App\Models\Child::STAR_3 }}% · ⭐⭐ ≥ {{ \App\Models\Child::STAR_2 }}% · ⭐ ≥ {{ \App\Models\Child::STAR_1 }}% — Streak 🔥 terhitung bila KPI ≥ {{ \App\Models\Child::STREAK_MIN }}%.</p>
    </div>
@endsection
