@extends('layouts.app')

@section('title', 'Masuk')
@section('page-title', 'KPI Anak')

@section('content')
    <div class="login-wrap">
        <div class="login-hero">
            <div class="login-logo">⭐</div>
            <h2>{{ config('app.name') }}</h2>
            <p class="muted">Pantau tugas rutin harian anak di rumah</p>
        </div>

        <form method="post" action="{{ route('login.attempt') }}" class="card form">
            @csrf
            @include('partials.errors')
            <label class="field">Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </label>
            <label class="field">Kata sandi
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
@endsection
