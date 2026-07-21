<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Baris tunggal pengaturan aplikasi (konfigurasi email dsb.) yg bisa diubah admin lewat UI tanpa edit .env. */
#[Fillable(['mail_mailer', 'resend_api_key', 'mail_from_address', 'mail_from_name'])]
class AppSetting extends Model
{
    /** Ambil (atau buat) baris pengaturan tunggal. */
    public static function current(): self
    {
        return self::query()->firstOrCreate([], ['mail_mailer' => 'log']);
    }
}
