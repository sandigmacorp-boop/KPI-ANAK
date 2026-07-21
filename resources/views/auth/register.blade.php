@extends('layouts.app')

@section('title', 'Daftar')
@section('page-title', config('app.name'))

@section('content')
    <div class="login-wrap">
        <div class="login-hero">
            <div class="login-logo">⭐</div>
            <h2>{{ config('app.name') }}</h2>
            <p class="muted">Buat akun keluarga baru — pantau tugas rutin harian anak di rumah</p>
        </div>

        <form method="post" action="{{ route('register.attempt') }}" class="card form">
            @csrf
            @include('partials.errors')
            <label class="field">Nama Anda
                <input name="name" value="{{ old('name') }}" maxlength="60" required autofocus placeholder="contoh: Budi">
            </label>
            <label class="field">Email
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            </label>
            <label class="field">Kata sandi <small class="muted">(min. 6 karakter)</small>
                <input type="password" name="password" required autocomplete="new-password">
            </label>
            <label class="field">Ulangi kata sandi
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </label>
            <button class="btn btn-primary btn-block">Buat Akun</button>
        </form>

        <p class="muted auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
    </div>
@endsection
