<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\FamilyGoal;
use App\Models\Household;
use App\Models\PointAdjustment;
use App\Models\Reward;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\TeamChallenge;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaman produksi: seeder ini membuat akun demo dengan kata sandi yang
        // dikenal publik (didokumentasikan di README). Jangan pernah jalan di luar
        // local/testing — akun asli dibuat lewat halaman /daftar.
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('Seeder demo dilewati: hanya boleh dijalankan di APP_ENV=local/testing. Gunakan halaman "Daftar" untuk membuat akun asli.');

            return;
        }

        $household = Household::create(['name' => 'Keluarga Sandigma']);

        $user = User::create([
            'household_id' => $household->id,
            'name' => 'Orang Tua',
            'email' => 'sandigmacorp@gmail.com',
            'password' => 'kpianak123',
        ]);

        $kakak = Child::create([
            'user_id' => $user->id,
            'household_id' => $household->id,
            'name' => 'Kakak',
            'emoji' => '🦁',
            'color' => '#7C3AED',
            'pet_type' => 'naga',
            'access_token' => Str::random(40),
        ]);

        $adik = Child::create([
            'user_id' => $user->id,
            'household_id' => $household->id,
            'name' => 'Adik',
            'emoji' => '🐰',
            'color' => '#D97706',
            'pet_type' => 'kucing',
            'access_token' => Str::random(40),
        ]);

        // [emoji, judul, poin, waktu, hari (null = setiap hari; 1=Sen .. 7=Min), wajib foto]
        $template = [
            ['🛏️', 'Merapikan tempat tidur', 10, 'pagi', null, true],
            ['🪥', 'Mandi & gosok gigi pagi', 10, 'pagi', null, false],
            ['🍳', 'Sarapan sendiri tanpa disuruh', 5, 'pagi', null, true],
            ['🧸', 'Membereskan mainan', 10, 'siang', null, true],
            ['😴', 'Tidur siang', 10, 'siang', [6, 7], false],
            ['📚', 'Mengerjakan PR', 20, 'sore', [1, 2, 3, 4, 5], true],
            ['📖', 'Membaca buku 15 menit', 15, 'sore', null, true],
            ['🤝', 'Membantu orang tua', 10, 'sore', null, true],
            ['🎒', 'Menyiapkan tas & seragam sekolah', 10, 'malam', [7, 1, 2, 3, 4], true],
            ['🪥', 'Gosok gigi sebelum tidur', 10, 'malam', null, false],
            ['🌙', 'Tidur maksimal jam 9 malam', 10, 'malam', null, false],
        ];

        foreach ([$kakak, $adik] as $child) {
            foreach ($template as $i => [$emoji, $title, $points, $slot, $days, $photo]) {
                Task::create([
                    'child_id' => $child->id,
                    'title' => $title,
                    'emoji' => $emoji,
                    'points' => $points,
                    'time_slot' => $slot,
                    'days' => $days,
                    'requires_photo' => $photo,
                    'sort_order' => $i,
                ]);
            }
        }

        // Katalog hadiah contoh — masing-masing punya harga poin sendiri.
        $esKrim = Reward::create(['child_id' => $kakak->id, 'title' => 'Es krim spesial', 'emoji' => '🍦', 'cost' => 150]);
        Reward::create(['child_id' => $kakak->id, 'title' => 'Main game ekstra 1 jam', 'emoji' => '🎮', 'cost' => 300]);
        Reward::create(['child_id' => $kakak->id, 'title' => 'Jalan-jalan ke taman', 'emoji' => '🎡', 'cost' => 500]);
        Reward::create(['child_id' => $adik->id, 'title' => 'Permen favorit', 'emoji' => '🍭', 'cost' => 50]);
        Reward::create(['child_id' => $adik->id, 'title' => 'Pilih menu makan malam', 'emoji' => '🍕', 'cost' => 250]);
        Reward::create(['child_id' => $adik->id, 'title' => 'Mainan baru', 'emoji' => '🧸', 'cost' => 400]);

        // Anak contoh "terdaftar" sejak seminggu lalu agar riwayat seed terhitung wajar.
        foreach ([$kakak, $adik] as $child) {
            $child->forceFill(['created_at' => today()->subDays(6)])->save();
        }

        // Riwayat 6 hari terakhir supaya laporan langsung ada isinya (deterministik).
        mt_srand(20260712);

        foreach ([$kakak, $adik] as $child) {
            for ($i = 6; $i >= 1; $i--) {
                $date = today()->subDays($i);

                foreach ($child->tasksForDate($date) as $task) {
                    if (mt_rand(1, 100) <= 85) {
                        TaskCompletion::create([
                            'task_id' => $task->id,
                            'child_id' => $child->id,
                            'date' => $date->toDateString(),
                            'completed_at' => $date->copy()->setTime(19, 30),
                        ]);
                    }
                }
            }
        }

        // Hari ini: si Kakak baru menyelesaikan tugas pagi.
        foreach ($kakak->tasksForDate(today())->where('time_slot', 'pagi') as $task) {
            TaskCompletion::create([
                'task_id' => $task->id,
                'child_id' => $kakak->id,
                'date' => today()->toDateString(),
                'completed_at' => now(),
            ]);
        }

        // Contoh: Kakak sudah menukar es krim, menunggu diberikan orang tua.
        $kakak->redeem($esKrim);

        // Contoh penyesuaian poin: bonus & pengurangan.
        PointAdjustment::create(['child_id' => $kakak->id, 'amount' => 20, 'reason' => 'Bantu cuci piring tanpa disuruh']);
        PointAdjustment::create(['child_id' => $kakak->id, 'amount' => -10, 'reason' => 'Lupa merapikan mainan']);
        PointAdjustment::create(['child_id' => $adik->id, 'amount' => 15, 'reason' => 'Berbagi mainan dengan kakak']);

        // XP peliharaan awal = total poin tugas yang sudah terkumpul, lalu berikan lencana yang layak.
        foreach ([$kakak, $adik] as $child) {
            $child->update(['pet_xp' => $child->totalPoints()]);
            $child->syncAchievements();
        }

        // Contoh mood 7 hari terakhir.
        $moodPattern = ['senang', 'semangat', 'biasa', 'lelah', 'senang', 'biasa', 'senang'];
        foreach ([$kakak, $adik] as $ci => $child) {
            foreach (range(6, 0) as $i) {
                $child->setMood($moodPattern[($i + $ci) % count($moodPattern)], today()->subDays($i));
            }
        }

        // Contoh tujuan keluarga (di-backdate agar poin seed ikut menyumbang).
        $goal = FamilyGoal::create([
            'household_id' => $household->id,
            'title' => 'Jalan-jalan ke pantai',
            'emoji' => '🏖️',
            'target' => 2000,
        ]);
        $goal->forceFill(['created_at' => today()->subDays(7)])->save();

        // Contoh tantangan kerja sama tim (masih terbuka, siap dicoba kirim laporan).
        TeamChallenge::create([
            'household_id' => $household->id,
            'title' => 'Bersihkan Garasi Bersama',
            'emoji' => '🧹',
            'description' => 'Rapikan & sapu garasi bersama-sama, lalu foto sebelum dan sesudah.',
            'points' => 50,
            'status' => 'open',
        ]);
    }
}
