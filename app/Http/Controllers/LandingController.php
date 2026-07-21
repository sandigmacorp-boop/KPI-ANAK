<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Landing page publik (iklan/penjelasan produk) — pintu masuk sebelum login/daftar. */
class LandingController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('home');
        }

        return view('landing');
    }
}
