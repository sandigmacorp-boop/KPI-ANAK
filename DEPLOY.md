# 🚀 Panduan Go-Live SANS FAMILY

Panduan memindahkan aplikasi dari laptop ke internet, supaya bisa diakses dari mana saja dengan **HTTPS** — yang sekaligus mengaktifkan fitur penuh PWA (ikon aplikasi mandiri di HP) dan **notifikasi pengingat di perangkat**.

## Pilih jalur dulu

| Jalur | Cocok untuk | Perkiraan biaya | Kesulitan |
|---|---|---|---|
| **A. Shared hosting cPanel** | Kebanyakan orang — sekali setup lalu lupakan | Rp 20–50 rb/bulan + domain ±Rp 150 rb/tahun | ⭐⭐ mudah |
| **B. VPS (server sendiri)** | Yang terbiasa Linux, ingin kontrol penuh | Rp 50–150 rb/bulan | ⭐⭐⭐⭐ |
| **C. Cloudflare Tunnel dari PC rumah** | Coba-coba dulu tanpa beli hosting — server tetap laptop Anda | **Gratis** (domain opsional) | ⭐⭐⭐ |

> Rekomendasi: mulai dari **Jalur A**. Aplikasi ini ringan (SQLite, tanpa build step), hosting termurah pun sanggup.

**Syarat minimum hosting:** PHP **8.2+** (idealnya 8.3/8.4) dengan ekstensi `pdo_sqlite`, `sqlite3`, `gd`, `mbstring`, `fileinfo`, `openssl`, `curl`, `zip` — semuanya standar di hosting cPanel Indonesia (Niagahoster, Hostinger, Rumahweb, DomaiNesia, IDCloudHost, dll). **Tidak butuh** Node.js, tidak butuh MySQL (kecuali Anda mau — lihat bagian MySQL).

---

## Langkah 0 — Buat paket upload (di laptop)

Klik dua kali **`buat-paket-hosting.bat`** di folder project. Skrip akan menghasilkan **`sans-family-hosting.zip`** yang sudah berisi:

- seluruh kode aplikasi + `vendor/` (jadi **tidak perlu Composer di server**),
- file `.env` produksi dengan `APP_KEY` baru, `APP_DEBUG=false`, timezone WIB,
- **database & foto bukti saat ini** (akun, anak, tugas, riwayat ikut pindah).

Ingin mulai dari nol di server? Jalankan dengan opsi: `deploy\buat-paket-hosting.ps1 -TanpaData` — lalu di server jalankan `php artisan migrate --seed` sekali.

---

## Jalur A — Shared Hosting cPanel

### 1. Beli hosting + domain
Paket termurah cukup. Saat memilih, pastikan tertulis mendukung **PHP 8.2+** dan ada fitur **SSL gratis (AutoSSL/Let's Encrypt)** — hampir semua punya.

### 2. Set versi PHP
cPanel → **Select PHP Version** (atau *MultiPHP Manager*) → pilih **8.3** atau **8.4** → di tab *Extensions* pastikan tercentang: `pdo_sqlite`, `sqlite3`, `gd`, `mbstring`, `fileinfo`, `curl`, `zip`.

### 3. Upload & ekstrak
1. cPanel → **File Manager** → masuk ke folder **home** (`/home/namauser/`, *bukan* `public_html`).
2. Upload `sans-family-hosting.zip` → klik kanan → **Extract** → beri nama folder **`sans-family`**.

Struktur akhirnya: `/home/namauser/sans-family/` berisi `app/`, `public/`, `vendor/`, `.env`, dst.

### 4. Arahkan domain ke folder `public`
Web root harus menunjuk ke **`sans-family/public`** — bukan ke folder aplikasi.

- **Cara utama:** cPanel → **Domains** → klik domain Anda → ubah **Document Root** menjadi `/home/namauser/sans-family/public` → simpan. (Di beberapa hosting menu ini bernama *Addon Domains* / *Manage Domains*.)
- **Plan B (bila document root domain utama terkunci):** biarkan `public_html` sebagai web root, lalu:
  1. Pindahkan **isi** folder `sans-family/public/` ke `public_html/` (termasuk `.htaccess`, `index.php`, `css/`, `js/`, `icons/`, `manifest.webmanifest`, `sw.js`).
  2. Edit `public_html/index.php`, ubah dua baris path-nya:
     ```php
     require __DIR__.'/../sans-family/vendor/autoload.php';
     $app = require_once __DIR__.'/../sans-family/bootstrap/app.php';
     ```

### 5. Edit `.env` di server
File Manager → folder `sans-family` → klik kanan `.env` → **Edit** (aktifkan *Show Hidden Files* bila tidak terlihat):

```env
APP_URL=https://domain-anda.com     ← ganti dengan domain asli
```

Sisanya sudah disetel otomatis oleh skrip paket (production, debug mati, WIB, SQLite).

### 6. Buat symlink foto bukti
- **Punya menu Terminal di cPanel** (kebanyakan hosting modern punya):
  ```bash
  cd ~/sans-family
  php artisan storage:link
  php artisan config:cache && php artisan route:cache && php artisan view:cache   # opsional, mempercepat
  ```
- **Tidak ada Terminal:** salin `sans-family/deploy/setup-storage-link.php` ke folder web root (langkah 4), lalu buka `https://domain-anda.com/setup-storage-link.php` sekali — file itu membuat symlink lalu menghapus dirinya sendiri. *(Untuk Plan B: edit dulu `$appDir` di dalam file itu.)*

### 7. Aktifkan HTTPS
cPanel → **SSL/TLS Status** → centang domain → **Run AutoSSL** (gratis, otomatis perpanjang). Setelah aktif, buka situs dengan `https://`. Banyak hosting juga punya tombol **Force HTTPS Redirect** di menu Domains — nyalakan.

### 8. Uji & amankan
1. Buka `https://domain-anda.com` → halaman login SANS FAMILY muncul.
2. Login → **Pengaturan → segera GANTI KATA SANDI** (paket membawa sandi lama).
3. Buka menu **Kelola → 🔗 Link** → **buat link mode anak yang BARU** untuk tiap anak (link lama dari laptop tersebar di jaringan rumah; di internet link = kunci akses anak).
4. Tes centang tugas + upload foto bukti + lihat fotonya.

### 9. Pasang di HP (sekarang jadi aplikasi penuh)
Karena sudah HTTPS: buka link mode anak di HP/tablet anak → Chrome menu ⋮ → **Install app / Tambahkan ke layar utama** → sekarang terbuka **fullscreen dengan ikon sendiri**. Di halaman tugas anak juga muncul tombol **“🔔 Aktifkan pengingat di perangkat ini”** — tekan dan izinkan, maka tiap ganti slot waktu (pagi/siang/sore/malam) muncul notifikasi daftar tugas selama aplikasi terbuka.

---

## Jalur B — VPS (Ubuntu 24.04 + Caddy)

Caddy dipilih karena HTTPS-nya full otomatis. Login SSH sebagai root:

```bash
# PHP + ekstensi
apt update && apt install -y php8.3-fpm php8.3-sqlite3 php8.3-gd php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip unzip

# Caddy
apt install -y debian-keyring debian-archive-keyring apt-transport-https curl
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list
apt update && apt install -y caddy

# Aplikasi (upload sans-family-hosting.zip via scp/SFTP ke /var/www)
cd /var/www && unzip sans-family-hosting.zip -d sans-family
cd sans-family
nano .env                        # isi APP_URL=https://domain-anda.com
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data /var/www/sans-family
```

`/etc/caddy/Caddyfile` (arahkan dulu DNS A-record domain ke IP VPS):

```
domain-anda.com {
    root * /var/www/sans-family/public
    php_fastcgi unix//run/php/php8.3-fpm.sock
    file_server
    encode gzip
}
```

```bash
systemctl reload caddy
```

Selesai — Caddy mengurus sertifikat HTTPS otomatis. Lanjut ke langkah **8–9 Jalur A** (ganti sandi, link anak baru, install PWA).

---

## Jalur C — Gratis: Cloudflare Tunnel dari PC rumah

Server tetap laptop Anda; Cloudflare memberi alamat **HTTPS publik** tanpa buka port/router. Konsekuensi: aplikasi hanya online **selama laptop menyala** dan server jalan.

1. Install: `winget install Cloudflare.cloudflared`
2. Jalankan aplikasi seperti biasa (`start-server.bat`), lalu di terminal lain:
   ```powershell
   cloudflared tunnel --url http://localhost:8000
   ```
3. Muncul alamat acak `https://xxxx.trycloudflare.com` → itu alamat publik Anda (HTTPS penuh, PWA & notifikasi jalan). Alamat berganti tiap kali tunnel dijalankan ulang — cocok untuk uji coba.
4. Ingin alamat tetap? Daftar akun Cloudflare gratis + arahkan domain Anda ke Cloudflare, lalu buat *named tunnel* (`cloudflared tunnel login` → `cloudflared tunnel create sans-family` → route DNS). Detail: [developers.cloudflare.com/cloudflare-one](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/).

> Karena alamatnya publik, tetap lakukan: ganti kata sandi + `.env` `APP_ENV=production`, `APP_DEBUG=false` bila tunnel dipakai jangka panjang.

---

## Opsional: memakai MySQL

SQLite sudah lebih dari cukup untuk satu keluarga (dan backup-nya semudah menyalin satu file). Namun bila ingin MySQL:

1. cPanel → **MySQL Databases** → buat database + user + password, hubungkan keduanya (ALL PRIVILEGES).
2. Edit `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=namauser_sansfamily
   DB_USERNAME=namauser_sans
   DB_PASSWORD=rahasia
   ```
3. Terminal: `php artisan migrate --seed` (data SQLite lama tidak otomatis pindah — mulai baru, atau minta bantuan migrasi data).

## Memindahkan data belakangan

Sudah terlanjur pakai di rumah lalu baru deploy? Cukup salin 2 hal dari laptop ke server (timpa):

- `database/database.sqlite` — semua akun, tugas, riwayat, poin.
- `storage/app/public/bukti/` — semua foto bukti.

(Atau jalankan ulang `buat-paket-hosting.bat` — paket selalu membawa data terbaru.)

## Checklist keamanan produksi

- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false` *(otomatis dari skrip paket)*
- [ ] Kata sandi orang tua sudah diganti dari bawaan
- [ ] Link mode anak dibuat ulang setelah online (Kelola → 🔗 Link → “Buat link baru”)
- [ ] HTTPS aktif & redirect dipaksa
- [ ] Jangan bagikan link mode anak di tempat publik — siapa pun yang memegangnya bisa mencentang tugas & menukar poin anak itu (data lain tetap aman)

## Backup & update aplikasi

- **Backup** = salin `database/database.sqlite` + folder `storage/app/public/bukti/` (via File Manager → Compress → download). Lakukan rutin, misalnya tiap minggu.
- **Update kode** (bila nanti ada perubahan): jalankan lagi `buat-paket-hosting.bat` → upload zip → ekstrak **kecuali** jangan menimpa `database/database.sqlite`, `storage/app/public/bukti/`, dan `.env` di server. Lalu di Terminal: `php artisan migrate && php artisan config:cache && php artisan route:cache && php artisan view:cache`.

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| **Error 500 / halaman putih** | Cek `sans-family/storage/logs/laravel.log`. Paling sering: folder `storage/` & `bootstrap/cache/` tidak writable → File Manager → klik kanan → Permissions → `755` rekursif (pemilik harus user hosting Anda). |
| **Tampilan berantakan / CSS 404** | Document root belum menunjuk ke `.../sans-family/public` (langkah 4). |
| **Foto bukti tidak muncul (403/404)** | Symlink belum dibuat → langkah 6. |
| **Upload foto gagal** | cPanel → *MultiPHP INI Editor* → naikkan `upload_max_filesize` & `post_max_size` ke `12M`, pastikan `upload_tmp_dir` kosong (default) atau menunjuk folder yang ada. |
| **Error 419 saat login** | Cookie/session: pastikan mengakses lewat domain (bukan campur IP), lalu muat ulang halaman login. |
| **Redirect https aneh saat SSL belum aktif** | Sementara set `FORCE_HTTPS=false` di `.env` → uji → hapus lagi setelah SSL aktif. |
| **Jam/tanggal meleset** | `.env` → `APP_TIMEZONE=Asia/Jakarta` (ganti ke `Asia/Makassar` / `Asia/Jayapura` sesuai domisili) lalu `php artisan config:cache`. |
