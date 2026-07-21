<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $child->color }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Tugas {{ $child->name }} · {{ config('app.name') }}</title>
    <link rel="manifest" href="{{ route('kid.manifest', $child->access_token) }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-180.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="kid-mode">
<div class="app">
    <header class="kid-hero" style="--child: {{ $child->color }}">
        <div class="avatar avatar-lg" aria-hidden="true">{{ $child->emoji }}</div>
        <h1>Halo, {{ $child->name }}!</h1>
        <p>{{ $day->translatedFormat('l, j F Y') }} · 🏅 {{ number_format($balance, 0, ',', '.') }} poin</p>
        <nav class="kid-tabs">
            <a class="active" href="{{ route('kid.show', $child->access_token) }}">📝 Tugasku</a>
            <a href="{{ route('kid.performa', $child->access_token) }}">
                📊 Performaku
                @if ($pendingCount > 0)
                    <span class="tab-badge">{{ $pendingCount }}</span>
                @endif
            </a>
        </nav>
    </header>

    <main class="content">
        @if ($slotNow && $slotPending->isNotEmpty())
            <div class="time-banner" role="status">
                ⏰ Waktunya tugas <b>{{ \App\Models\Task::SLOTS[$slotNow]['label'] }}</b>!
                Ada {{ $slotPending->count() }} tugas menunggumu.
            </div>
        @endif

        @if ($pendingCount > 0)
            <a class="reward-banner" href="{{ route('kid.performa', $child->access_token) }}">
                ⏳ {{ $pendingCount }} hadiah menunggu diberikan — lihat →
            </a>
        @elseif ($affordableCount > 0)
            <a class="reward-banner pop" href="{{ route('kid.performa', $child->access_token) }}">
                🎉 Poinmu cukup untuk menukar hadiah! Tukar sekarang →
            </a>
        @endif

        @include('partials.pet-card')

        @include('partials.family-goal', ['goal' => $familyGoal])

        @include('partials.mood-card', ['moodUrl' => route('kid.mood', $child->access_token)])

        @include('partials.weekly-challenge')

        @include('partials.team-challenges')

        @include('partials.checklist-board', ['mode' => 'kid'])

        <button type="button" id="enable-notif" class="btn btn-ghost btn-block" hidden
                data-reminder-url="{{ route('kid.reminder', $child->access_token) }}">
            🔔 Aktifkan pengingat di perangkat ini
        </button>

        <p class="kid-foot muted">⭐ {{ config('app.name') }}</p>
    </main>
</div>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
</body>
</html>
