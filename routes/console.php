<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat tugas ke Telegram orang tua pada tiap slot waktu (zona WIB).
Schedule::command('sans:reminders')->dailyAt('06:30')->timezone('Asia/Jakarta'); // pagi
Schedule::command('sans:reminders')->dailyAt('12:00')->timezone('Asia/Jakarta'); // siang
Schedule::command('sans:reminders')->dailyAt('16:00')->timezone('Asia/Jakarta'); // sore
Schedule::command('sans:reminders')->dailyAt('19:30')->timezone('Asia/Jakarta'); // malam

// Rekap mingguan tiap Minggu malam.
Schedule::command('sans:weekly-recap')->sundays()->at('19:00')->timezone('Asia/Jakarta');
