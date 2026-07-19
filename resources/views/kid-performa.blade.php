<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $child->color }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Performa {{ $child->name }} · {{ config('app.name') }}</title>
    <link rel="manifest" href="{{ route('kid.manifest', $child->access_token) }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-180.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="kid-mode">
<div class="app">
    <header class="kid-hero" style="--child: {{ $child->color }}">
        <div class="avatar avatar-lg" aria-hidden="true">{{ $child->emoji }}</div>
        <h1>Performa {{ $child->name }}</h1>
        <p>{{ $day->translatedFormat('l, j F Y') }}</p>
        <nav class="kid-tabs">
            <a href="{{ route('kid.show', $child->access_token) }}">📝 Tugasku</a>
            <a class="active" href="{{ route('kid.performa', $child->access_token) }}">
                📊 Performaku
                @if ($pendingRedemptions->isNotEmpty())
                    <span class="tab-badge">{{ $pendingRedemptions->count() }}</span>
                @endif
            </a>
        </nav>
    </header>

    <main class="content" style="--child: {{ $child->color }}">

        @if (session('ok'))
            <div class="flash" role="status">{{ session('ok') }}</div>
        @endif
        @if (session('err'))
            <div class="errors" role="alert">{{ session('err') }}</div>
        @endif

        @include('partials.pet-card')

        {{-- Saldo poin --}}
        <section class="card balance-hero">
            <span class="balance-num">🏅 {{ number_format($balance, 0, ',', '.') }}</span>
            <span class="stat-cap">Poinku sekarang</span>
            <span class="balance-sub">@include('partials.balance-breakdown', ['b' => $breakdown])</span>
        </section>

        {{-- Bonus & pengurangan poin dari orang tua --}}
        @if ($adjustments->isNotEmpty())
            <section class="card">
                <h3 class="card-title">🎉 Bonus & Catatan Poin</h3>
                @foreach ($adjustments as $adj)
                    <div class="reward-row">
                        <span class="reward-emoji" aria-hidden="true">{{ $adj->emoji() }}</span>
                        <span class="reward-body">
                            <span class="reward-title">{{ $adj->reason ?: ($adj->isBonus() ? 'Bonus poin' : 'Pengurangan poin') }}</span>
                            <span class="muted">{{ $adj->created_at->translatedFormat('j M Y') }}</span>
                        </span>
                        <span class="adj-amount {{ $adj->isBonus() ? 'plus' : 'minus' }}">{{ $adj->signed() }}</span>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Katalog tukar poin --}}
        <section class="card">
            <h3 class="card-title">🎁 Tukar Poinku</h3>
            @if ($catalog->isEmpty())
                <p class="muted">Katalog hadiah masih kosong. Minta Ayah/Bunda mengisinya ya! 🎁</p>
            @endif
            @foreach ($catalog as $reward)
                @php($affordable = $balance >= $reward->cost)
                <div class="reward-row">
                    <span class="reward-emoji" aria-hidden="true">{{ $reward->emoji }}</span>
                    <span class="reward-body">
                        <span class="reward-title">{{ $reward->title }}</span>
                        <span class="muted">🏅 {{ number_format($reward->cost, 0, ',', '.') }} poin</span>
                        @unless ($affordable)
                            <span class="bar bar-mini"><span class="bar-fill" style="width: {{ (int) round(min($balance, $reward->cost) / max($reward->cost, 1) * 100) }}%"></span></span>
                            <span class="reward-progress-text muted">kurang {{ number_format($reward->cost - $balance, 0, ',', '.') }} poin lagi</span>
                        @endunless
                    </span>
                    @if ($affordable)
                        <form method="post" action="{{ route('kid.redeem', [$child->access_token, $reward]) }}"
                              data-confirm="Tukar {{ $reward->cost }} poin dengan {{ $reward->emoji }} {{ $reward->title }}?">
                            @csrf
                            <button class="btn btn-primary btn-sm">Tukar</button>
                        </form>
                    @else
                        <button class="btn btn-ghost btn-sm" disabled>Tukar</button>
                    @endif
                </div>
            @endforeach
        </section>

        {{-- Menunggu diberikan --}}
        @if ($pendingRedemptions->isNotEmpty())
            <section class="card reward-card reward-unlocked">
                <h3 class="card-title">⏳ Menunggu Diberikan</h3>
                @foreach ($pendingRedemptions as $redemption)
                    <div class="reward-row">
                        <span class="reward-emoji" aria-hidden="true">{{ $redemption->emoji }}</span>
                        <span class="reward-body">
                            <span class="reward-title">{{ $redemption->title }}</span>
                            <span class="muted">ditukar {{ $redemption->created_at->translatedFormat('j M, H:i') }} — tunjukkan ke Ayah/Bunda ya! 😊</span>
                        </span>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Hari ini --}}
        <section class="card">
            <h3 class="card-title">Hari Ini</h3>
            <div class="child-head">
                @include('partials.ring', ['percent' => $stats['percent']])
                <div class="child-meta">
                    <div class="chips">
                        <span class="chip">✅ {{ $stats['done_tasks'] }}/{{ $stats['total_tasks'] }} tugas</span>
                        <span class="chip">🏅 {{ $stats['earned_points'] }}/{{ $stats['total_points'] }} poin</span>
                    </div>
                    @include('partials.stars', ['stars' => $stars])
                </div>
            </div>
        </section>

        <div class="stat-grid">
            <div class="card stat">
                <span class="stat-num">🔥 {{ $streak }}</span>
                <span class="stat-cap">Hari beruntun</span>
            </div>
            <div class="card stat">
                <span class="stat-num">📈 {{ $avg7 === null ? '—' : $avg7.'%' }}</span>
                <span class="stat-cap">Rata-rata 7 hari</span>
            </div>
        </div>

        <section class="card">
            <h3 class="card-title">Minggu Ini</h3>
            @include('partials.week-bars')
        </section>

        @include('partials.achievements')

        @if ($history->isNotEmpty())
            <details class="numbers">
                <summary>Hadiah yang pernah kudapat ({{ $history->where('canceled_at', null)->count() }})</summary>
                @foreach ($history as $redemption)
                    <div class="reward-row {{ $redemption->canceled_at ? 'claimed' : '' }}">
                        <span class="reward-emoji" aria-hidden="true">{{ $redemption->emoji }}</span>
                        <span class="reward-body">
                            <span class="reward-title">{{ $redemption->title }}</span>
                            <span class="muted">{{ $redemption->canceled_at ? '↩️ dibatalkan, poin kembali' : '✅ '.$redemption->delivered_at->translatedFormat('j F Y') }}</span>
                        </span>
                    </div>
                @endforeach
            </details>
        @endif

        <p class="kid-foot muted">⭐ {{ config('app.name') }}</p>
    </main>
</div>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
</body>
</html>
