<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Setiap pendaftar baru mendapat keluarga (household) sendiri — terisolasi
        // penuh dari keluarga lain (anak, tugas, poin, dst. tak saling terlihat).
        $household = Household::create(['name' => 'Keluarga '.$data['name']]);

        // last_login_at & is_admin sengaja TIDAK di $fillable (bukan input pengguna) —
        // diisi via forceFill agar aman dari mass-assignment.
        $user = User::create([
            'household_id' => $household->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->forceFill(['last_login_at' => now()])->save();

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()->route('home')
            ->with('ok', 'Akun berhasil dibuat! Selamat datang di '.config('app.name').' 🎉 Yuk tambahkan anak pertama Anda.');
    }
}
