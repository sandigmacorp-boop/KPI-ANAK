<?php

namespace App\Http\Controllers;

/** Halaman preview publik: tur screenshot 11 halaman aplikasi, buat gambaran orang tua sebelum daftar. */
class TurController extends Controller
{
    public function show()
    {
        return view('tur');
    }
}
