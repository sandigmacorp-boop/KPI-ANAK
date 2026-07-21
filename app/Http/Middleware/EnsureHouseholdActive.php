<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Tolak & langsung logout kalau keluarga pemilik sesi ini sudah dinonaktifkan admin. */
class EnsureHouseholdActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->household?->isDisabled()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun keluarga ini telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.']);
        }

        return $next($request);
    }
}
