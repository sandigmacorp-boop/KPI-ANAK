<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email');
    }

    /** Ditautkan lewat link bertanda tangan di email verifikasi. */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill(); // tandai terverifikasi + pancarkan event Verified (idempoten)

        return redirect()->route('home')->with('ok', 'Email berhasil diverifikasi! ✅');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('ok', 'Link verifikasi baru sudah dikirim ke email Anda.');
    }
}
