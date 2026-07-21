<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/** Pengaturan pengirim email (Resend) lewat UI admin, agar tak perlu edit .env di server. */
class AdminMailSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.email-settings', ['settings' => AppSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_mailer' => ['required', Rule::in(['log', 'resend'])],
            'resend_api_key' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:100'],
        ]);

        $settings = AppSetting::current();

        // Field kunci API sengaja dikosongkan di form (tak pernah tampilkan rahasia balik ke
        // halaman) — hanya timpa kunci lama kalau admin benar-benar isi nilai baru.
        if (filled($data['resend_api_key'] ?? null)) {
            $settings->resend_api_key = $data['resend_api_key'];
        }

        $settings->mail_mailer = $data['mail_mailer'];
        $settings->mail_from_address = $data['mail_from_address'] ?? null;
        $settings->mail_from_name = $data['mail_from_name'] ?? null;
        $settings->save();

        if ($settings->mail_mailer === 'resend' && blank($settings->resend_api_key)) {
            return redirect()->route('admin.email.edit')
                ->with('err', 'Pengirim diset ke Resend tapi API key belum diisi — email belum akan terkirim sungguhan.');
        }

        return redirect()->route('admin.email.edit')->with('ok', 'Pengaturan email disimpan.');
    }

    /** Kirim email tes ke alamat admin sendiri, memastikan konfigurasi benar-benar berfungsi. */
    public function test(Request $request)
    {
        $settings = AppSetting::current();

        if ($settings->mail_mailer === 'resend' && blank($settings->resend_api_key)) {
            return redirect()->route('admin.email.edit')->with('err', 'Isi & simpan API key Resend dulu sebelum tes.');
        }

        try {
            Mail::raw(
                'Ini email tes dari '.config('app.name').'. Kalau kamu menerima ini, konfigurasi email sudah benar! ✅',
                fn ($msg) => $msg->to($request->user()->email)->subject('Email Tes · '.config('app.name')),
            );
        } catch (\Throwable $e) {
            return redirect()->route('admin.email.edit')->with('err', 'Gagal mengirim: '.$e->getMessage());
        }

        return redirect()->route('admin.email.edit')
            ->with('ok', "Email tes dikirim ke {$request->user()->email} — cek inbox (atau folder spam).");
    }
}
