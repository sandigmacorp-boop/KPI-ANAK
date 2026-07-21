@extends('layouts.app')

@section('title', 'Verifikasi Email')
@section('page-title', config('app.name'))

@section('content')
    <div class="login-wrap">
        <div class="login-hero">
            <div class="login-logo">📧</div>
            <h2>Cek Email Anda</h2>
            <p class="muted">Kami sudah mengirim link verifikasi ke <b>{{ auth()->user()->email }}</b>. Klik link itu untuk memverifikasi akun.</p>
        </div>

        @if (session('ok'))
            <div class="flash" role="status">✅ {{ session('ok') }}</div>
        @endif

        <div class="card form">
            <p class="muted">Tidak menerima email? Cek folder Spam, atau kirim ulang:</p>
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn btn-primary btn-block">🔁 Kirim Ulang Link Verifikasi</button>
            </form>
        </div>

        <p class="muted auth-switch"><a href="{{ route('home') }}">← Kembali ke Beranda</a></p>
    </div>
@endsection
