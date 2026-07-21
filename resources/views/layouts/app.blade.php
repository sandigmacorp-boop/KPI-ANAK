<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#7C3AED">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', 'Beranda') · {{ config('app.name') }}</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-180.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body @if($errors->any() && old('_form')) data-reopen="{{ old('_form') }}" @endif>
<div class="app">
    <header class="topbar">
        @hasSection('back')
            <a class="iconbtn" href="@yield('back')" aria-label="Kembali">‹</a>
        @endif
        <h1 class="topbar-title">@yield('page-title', config('app.name'))</h1>
    </header>

    <main class="content {{ auth()->check() ? 'with-nav' : '' }}">
        @if (session('ok'))
            <div class="flash" role="status">✅ {{ session('ok') }}</div>
        @endif
        @if (session('err'))
            <div class="errors" role="alert">{{ session('err') }}</div>
        @endif

        @auth
            @unless (auth()->user()->hasVerifiedEmail())
                <div class="verify-banner" role="status">
                    <span>📧 Verifikasi email Anda ({{ auth()->user()->email }}) untuk keamanan akun.</span>
                    <form method="post" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="linklike">Kirim ulang link</button>
                    </form>
                </div>
            @endunless
        @endauth

        @yield('content')
    </main>

    @auth
        <nav class="bottomnav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home', 'checklist', 'report') ? 'active' : '' }}">
                <span class="nav-ico">🏠</span>Beranda
            </a>
            <a href="{{ route('children.index') }}" class="{{ request()->routeIs('children.*', 'tasks.*') ? 'active' : '' }}">
                <span class="nav-ico">🧒</span>Kelola
            </a>
            <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">
                <span class="nav-ico">⚙️</span>Pengaturan
            </a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <span class="nav-ico">🛡️</span>Admin
                </a>
            @endif
        </nav>
    @endauth
</div>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
