@extends('layouts.app')

@section('title', 'Pengaturan Email')
@section('page-title', '📧 Pengaturan Email')
@section('back', route('admin.dashboard'))

@section('content')
    @include('partials.errors')

    <section class="card form">
        <h3 class="card-title">📧 Pengirim Email (Resend)</h3>
        <p class="muted">
            Atur di sini tanpa perlu edit file <code>.env</code> di server. Pilih <b>Log (tanpa kirim)</b> selama
            pengembangan — email verifikasi cuma ditulis ke log, tidak benar-benar terkirim.
        </p>

        <form method="post" action="{{ route('admin.email.update') }}">
            @csrf
            @method('PUT')

            <label class="field">Pengirim
                <select name="mail_mailer" required>
                    <option value="log" @selected(old('mail_mailer', $settings->mail_mailer) === 'log')>Log (tanpa kirim — mode pengembangan)</option>
                    <option value="resend" @selected(old('mail_mailer', $settings->mail_mailer) === 'resend')>Resend (kirim email sungguhan)</option>
                </select>
            </label>

            <label class="field">Resend API Key
                <input type="password" name="resend_api_key" autocomplete="off"
                       placeholder="{{ $settings->resend_api_key ? 'Sudah diisi — kosongkan jika tak ingin mengubah' : 're_xxxxxxxxxxxxxxxxxxxx' }}">
            </label>
            <p class="muted" style="margin-top: -8px;">
                @if ($settings->resend_api_key)
                    ✅ API key tersimpan (berakhiran ••{{ substr($settings->resend_api_key, -4) }}).
                @else
                    Belum diisi. Daftar gratis di <a href="https://resend.com" target="_blank" rel="noopener">resend.com</a> untuk mendapatkan key.
                @endif
            </p>

            <label class="field">Alamat Pengirim
                <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings->mail_from_address) }}" placeholder="noreply@domainkamu.com">
            </label>
            <label class="field">Nama Pengirim
                <input name="mail_from_name" value="{{ old('mail_from_name', $settings->mail_from_name) }}" placeholder="{{ config('app.name') }}">
            </label>

            <button class="btn btn-primary btn-block">Simpan Pengaturan</button>
        </form>

        @if ($settings->mail_mailer === 'resend' && $settings->resend_api_key)
            <form method="post" action="{{ route('admin.email.test') }}" style="margin-top: 12px;">
                @csrf
                <button class="btn btn-ghost btn-block">🔔 Kirim Email Tes ke Saya</button>
            </form>
        @endif
    </section>

    <section class="card">
        <h3 class="card-title">ℹ️ Catatan</h3>
        <p class="muted">
            Domain di "Alamat Pengirim" (bagian setelah @) harus sudah diverifikasi di dashboard Resend sebelum
            email benar-benar terkirim ke penerima selain akun Resend kamu sendiri.
        </p>
    </section>
@endsection
