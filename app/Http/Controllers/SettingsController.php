<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function show(Request $request)
    {
        return view('pengaturan', [
            'children' => $request->user()->children()->orderBy('created_at')->get(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ]);

        $request->user()->update($data);

        return redirect()->route('settings')->with('ok', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('settings')->with('ok', 'Kata sandi berhasil diganti.');
    }
}
