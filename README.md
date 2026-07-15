# ⭐ SANS FAMILY

Aplikasi KPI anak untuk memantau **tugas rutin harian** di rumah — checklist harian dengan bukti foto, poin, KPI %, bintang, streak, pengingat, dan **penukaran poin ke hadiah**. Backend **Laravel 13** (SQLite, tanpa setup database), tampilan **web app mobile-first (PWA)** yang bisa dibuka dari HP dan dipasang ke home screen — tanpa perlu build APK.

## Menjalankan

Cara paling gampang: klik dua kali **`start-server.bat`** — jendela akan menampilkan alamat untuk laptop dan untuk HP, lalu server berjalan.

Manual:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

> Saat pertama kali Windows bertanya soal firewall untuk PHP, pilih **Allow access** supaya HP bisa mengakses.

## Login awal

| | |
|---|---|
| URL | `http://localhost:8000` |
| Email | `sandigmacorp@gmail.com` |
| Kata sandi | `kpianak123` |

⚠️ **Ganti kata sandi** lewat menu *Pengaturan* setelah login pertama.

Database berisi data contoh (anak "Kakak" & "Adik" + tugas + riwayat seminggu). Untuk mulai dari kosong atau reset total:

```bash
php artisan migrate:fresh --seed
```

## Membuka dari HP

1. Pastikan HP dan laptop tersambung ke **WiFi yang sama**.
2. Jalankan `start-server.bat` — catat alamat `http://192.168.x.x:8000` yang ditampilkan.
3. Buka alamat itu di Chrome HP, lalu menu ⋮ → **Tambahkan ke layar utama** agar tampil seperti aplikasi.

> Catatan: fitur install PWA penuh (ikon + jendela mandiri + offline) aktif otomatis di `localhost` atau HTTPS. Lewat alamat WiFi biasa (HTTP), "Tambahkan ke layar utama" tetap berfungsi sebagai pintasan — semua fitur aplikasi jalan 100%.

## Go-live ke internet (hosting)

Ingin bisa diakses dari mana saja + PWA & notifikasi penuh? Ikuti **[DEPLOY.md](DEPLOY.md)** — panduan lengkap tiga jalur (shared hosting cPanel, VPS, atau gratis via Cloudflare Tunnel). Mulai dengan klik dua kali **`buat-paket-hosting.bat`** untuk membuat `sans-family-hosting.zip` yang siap upload (sudah berisi vendor, `.env` produksi, dan data Anda).

## Cara pakai

- **Beranda** — ringkasan hari ini per anak: KPI %, poin, bintang, streak 🔥, dan tanda 🎁 bila ada hadiah terbuka.
- **Checklist** — centang tugas anak; bisa mundur ke tanggal sebelumnya untuk koreksi.
- **📷 Bukti foto** — tugas bisa ditandai *wajib foto*. Saat anak mencentang dari mode anak, kamera/galeri terbuka dan foto bukti **wajib** dilampirkan (otomatis di-resize). Orang tua boleh mencentang tanpa foto. Ketuk thumbnail untuk melihat foto; membatalkan centang menghapus fotonya.
- **Mode Anak** 🔗 — tiap anak punya link rahasia (`/c/xxxx`) yang bisa dibuka di HP/tablet si anak **tanpa login**; anak hanya bisa mencentang tugas **hari ini**. Salin link dari Beranda, Kelola, atau Pengaturan. Kalau link bocor, buat link baru dari menu Kelola → 🔗 Link.
- **📊 Performaku (dashboard anak)** — di mode anak ada tab *Performaku*: saldo poin besar, katalog hadiah, ring KPI hari ini, streak, grafik seminggu, dan riwayat hadiah.
- **🎁 Tukar poin ke hadiah** — orang tua mengisi **katalog hadiah per anak** lewat Kelola → 🎁 Hadiah; **setiap hadiah punya harga poin sendiri** (mis. 🍦 Es krim 150 poin). Saldo anak = poin terkumpul − poin ditukar. Anak menekan **Tukar** di Performaku (tombol otomatis nonaktif + "kurang X poin lagi" bila saldo kurang) → masuk antrean **Perlu Diberikan** di sisi orang tua → orang tua menandai *sudah diberikan* atau *membatalkan* (poin kembali). Hadiah bisa disembunyikan dari katalog tanpa menghapus riwayat.
- **🔔 Pengingat** — aplikasi tahu slot waktu berjalan (pagi 04–11, siang 11–15, sore 15–18.30, malam 18.30–24): banner *"⏰ Waktunya tugas Malam!"* + label *"sekarang"* di checklist anak, chip *"⏰ n tugas belum"* di Beranda orang tua, dan halaman anak menyegarkan diri otomatis saat ganti slot / dibuka lagi. Di `localhost`/HTTPS tersedia juga tombol **"🔔 Aktifkan pengingat di perangkat ini"** untuk notifikasi perangkat selama aplikasi terbuka (di HTTP LAN, browser tidak mengizinkan Notification API — banner dalam aplikasi tetap jalan penuh).
- **Kelola** — tambah/edit anak (avatar & warna) dan tugasnya: ikon, poin, waktu (pagi/siang/sore/malam), jadwal hari, wajib foto atau tidak. Tugas bisa **dinonaktifkan** (riwayat tetap ada) — lebih aman daripada dihapus.
- **Laporan** — grafik KPI 7 hari, rata-rata 30 hari, total poin, hari bintang-3, ketuntasan per tugas, saldo poin, dan penukaran yang menunggu diberikan.

### Aturan KPI

- Setiap tugas punya **poin**. KPI harian = poin selesai ÷ total poin terjadwal hari itu.
- Bintang harian: ⭐⭐⭐ ≥ 90% · ⭐⭐ ≥ 70% · ⭐ ≥ 40%.
- **Streak** bertambah bila KPI harian ≥ 80% (hari ini yang belum tuntas tidak memutus streak; hari tanpa tugas bersifat netral).
- Ambang bisa diubah di konstanta [app/Models/Child.php](app/Models/Child.php) (`STAR_3`, `STAR_2`, `STAR_1`, `STREAK_MIN`).

## Struktur penting

```
app/Models/Child.php          # inti logika KPI, bintang, streak, saldo poin & penukaran
app/Models/Task.php           # jadwal tugas (slot waktu + jam slot + hari, wajib foto)
app/Models/Reward.php         # katalog hadiah (harga poin per hadiah)
app/Models/Redemption.php     # transaksi penukaran poin (snapshot hadiah)
app/Support/ProofPhoto.php    # simpan/resize/hapus foto bukti (GD)
app/Http/Controllers/         # Auth, Dashboard, Checklist, Kid (mode anak), Children, Tasks, Rewards, Report, Settings
resources/views/              # Blade: dashboard, checklist, kid, kid-performa, kelola/*, laporan, pengaturan
public/css/app.css            # seluruh styling (tanpa build step)
public/js/app.js              # toggle AJAX + upload foto, lightbox, dialog, toast, salin link
public/manifest.webmanifest   # PWA (+ manifest dinamis per anak di /c/{token}/manifest.webmanifest)
storage/app/public/bukti/     # file foto bukti (diakses via /storage, butuh `php artisan storage:link`)
database/seeders/             # akun + data contoh
```

Zona waktu default **Asia/Jakarta** (`APP_TIMEZONE` di `.env`).

### Troubleshooting

- **Upload foto gagal** ("unable to create a temporary file"): pastikan `upload_tmp_dir` di php.ini menunjuk folder yang ada. Di mesin ini sudah di-set ke `C:\Users\Legion 5\AppData\Local\Temp` pada php.ini Herd.
- **Foto tidak tampil (403/404)**: jalankan `php artisan storage:link` sekali (sudah dilakukan di project ini).

## Pengembangan berikutnya (opsional)

- Dibungkus jadi APK dengan Capacitor bila butuh aplikasi Android "asli".
- Web Push (notifikasi saat aplikasi tertutup) setelah online dengan HTTPS.
- Multi orang tua / wali dengan akun terpisah.
