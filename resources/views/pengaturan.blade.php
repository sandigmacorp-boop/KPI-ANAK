@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')

@section('content')
    @include('partials.errors')

    <section class="card form">
        <h3 class="card-title">👤 Profil</h3>
        <form method="post" action="{{ route('settings.profile') }}">
            @csrf
            <label class="field">Nama
                <input name="name" value="{{ old('name', auth()->user()->name) }}" maxlength="60" required>
            </label>
            <label class="field">Email
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
            </label>
            <button class="btn btn-primary btn-block">Simpan Profil</button>
        </form>
    </section>

    <section class="card form">
        <h3 class="card-title">🔒 Ganti Kata Sandi</h3>
        <form method="post" action="{{ route('settings.password') }}">
            @csrf
            <label class="field">Kata sandi saat ini
                <input type="password" name="current_password" required autocomplete="current-password">
            </label>
            <label class="field">Kata sandi baru <small class="muted">(min. 6 karakter)</small>
                <input type="password" name="password" required autocomplete="new-password">
            </label>
            <label class="field">Ulangi kata sandi baru
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </label>
            <button class="btn btn-primary btn-block">Ganti Kata Sandi</button>
        </form>
    </section>

    <section class="card form">
        <h3 class="card-title">📨 Notifikasi Telegram</h3>
        @if (! $telegram['enabled'])
            <p class="muted">Bot Telegram belum dikonfigurasi di server. Isi <code>TELEGRAM_BOT_TOKEN</code> di file <code>.env</code> server (lihat panduan di README/DEPLOY).</p>
        @elseif ($telegram['linked'])
            <p>✅ Telegram <b>terhubung</b>. Kamu menerima pengingat tugas tiap slot waktu & rekap mingguan.</p>
            <div class="row-actions">
                <form method="post" action="{{ route('settings.telegram.test') }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm">🔔 Kirim tes</button>
                </form>
                <form method="post" action="{{ route('settings.telegram.unlink') }}" data-confirm="Putuskan koneksi Telegram?">
                    @csrf
                    <button class="btn btn-danger btn-sm">Putuskan</button>
                </form>
            </div>
        @elseif ($telegram['deep_link'])
            <p class="muted">Dapatkan pengingat tugas & rekap mingguan lewat Telegram — tetap masuk walau aplikasi tertutup.</p>
            <ol class="tg-steps">
                <li>Buka bot lalu tekan <b>START</b>: <a href="{{ $telegram['deep_link'] }}" target="_blank" rel="noopener">{{ '@'.$telegram['bot'] }} ↗</a></li>
                <li>Kembali ke sini dan tekan tombol di bawah.</li>
            </ol>
            <form method="post" action="{{ route('settings.telegram.link') }}">
                @csrf
                <button class="btn btn-primary btn-block">✅ Saya sudah tekan START — Hubungkan</button>
            </form>
        @else
            <p class="errors">Token bot terdeteksi, tapi info bot tak terbaca. Pastikan <code>TELEGRAM_BOT_TOKEN</code> benar.</p>
        @endif
    </section>

    @if ($children->isNotEmpty())
        <section class="card form">
            <h3 class="card-title">🔗 Link Mode Anak</h3>
            <p class="muted">Bagikan ke perangkat masing-masing anak. Tanpa login — hanya bisa mencentang tugas hari ini.</p>
            @foreach ($children as $child)
                <div class="kid-link-row">
                    <span class="kid-link-name">{{ $child->emoji }} {{ $child->name }}</span>
                    <button type="button" class="linklike" data-copy="{{ $child->kidUrl() }}">📋 Salin</button>
                    <a href="{{ $child->kidUrl() }}" target="_blank" rel="noopener">Buka ↗</a>
                </div>
            @endforeach
        </section>
    @endif

    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-ghost btn-block">🚪 Keluar</button>
    </form>

    <p class="legend muted">{{ config('app.name') }} · Laravel {{ app()->version() }}</p>
@endsection
