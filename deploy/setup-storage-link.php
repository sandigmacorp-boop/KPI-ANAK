<?php

/*
|--------------------------------------------------------------------------
| Pembuat symlink storage — SEKALI PAKAI
|--------------------------------------------------------------------------
| Gunakan HANYA bila hosting Anda tidak menyediakan Terminal untuk
| menjalankan `php artisan storage:link`.
|
| Cara pakai:
|   1. Salin file ini ke folder web root (folder `public` aplikasi,
|      atau `public_html` bila Anda memakai Plan B di DEPLOY.md).
|   2. Buka  https://domain-anda.com/setup-storage-link.php  di browser.
|   3. Setelah pesan "BERHASIL", file ini menghapus dirinya sendiri.
*/

// Bila folder aplikasi TIDAK berada satu tingkat di atas file ini,
// ubah baris berikut, contoh: $appDir = '/home/namauser/sans-family';
$appDir = dirname(__DIR__);

header('Content-Type: text/plain; charset=utf-8');

$target = $appDir.'/storage/app/public';
$link = __DIR__.'/storage';

if (! is_dir($target)) {
    exit("GAGAL: folder aplikasi tidak ditemukan di: $target\n".
        "Edit variabel \$appDir di dalam file ini agar menunjuk ke folder aplikasi SANS FAMILY.");
}

if (is_link($link) || is_dir($link)) {
    @unlink(__FILE__);
    exit("Symlink storage sudah ada — tidak ada yang perlu dilakukan.\nFile ini sudah menghapus dirinya sendiri.");
}

if (! function_exists('symlink')) {
    exit("GAGAL: fungsi symlink() dinonaktifkan oleh hosting.\n".
        "Minta bantuan support hosting untuk menjalankan:\n  ln -s $target $link");
}

if (@symlink($target, $link)) {
    @unlink(__FILE__);
    echo "BERHASIL ✔  Symlink public/storage sudah dibuat.\n";
    echo "File setup ini sudah menghapus dirinya sendiri.\n";
    echo "Foto bukti sekarang bisa diakses. Selamat memakai SANS FAMILY!";
} else {
    echo "GAGAL membuat symlink (izin ditolak oleh server).\n".
        "Minta bantuan support hosting untuk menjalankan:\n  ln -s $target $link";
}
