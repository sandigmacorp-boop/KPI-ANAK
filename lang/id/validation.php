<?php

return [
    'required' => 'Kolom :attribute wajib diisi.',
    'required_unless' => 'Kolom :attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'integer' => ':attribute harus berupa angka.',
    'boolean' => ':attribute tidak valid.',
    'array' => ':attribute tidak valid.',
    'in' => 'Pilihan :attribute tidak valid.',
    'unique' => ':attribute sudah terpakai.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi saat ini salah.',
    'date_format' => 'Format :attribute tidak valid.',

    'between' => [
        'numeric' => ':attribute harus antara :min sampai :max.',
    ],
    'min' => [
        'string' => ':attribute minimal :min karakter.',
        'numeric' => ':attribute minimal :min.',
        'array' => 'Pilih minimal :min pada :attribute.',
    ],
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
        'numeric' => ':attribute maksimal :max.',
    ],

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'kata sandi baru',
        'current_password' => 'kata sandi saat ini',
        'title' => 'nama tugas',
        'points' => 'poin',
        'time_slot' => 'waktu',
        'days' => 'hari',
        'emoji' => 'emoji',
        'color' => 'warna',
        'date' => 'tanggal',
    ],
];
