<?php

/*
|--------------------------------------------------------------------------
| Diagnosis server SANS FAMILY — SEKALI PAKAI
|--------------------------------------------------------------------------
| 1. Upload file ini ke folder web root subdomain (folder `public` aplikasi).
| 2. Buka  https://sans.sandigma.com/cek-server.php  di browser.
| 3. Salin/screenshot hasilnya.
| 4. HAPUS file ini setelah selesai!
|
| Ditulis dengan sintaks PHP lama agar tetap jalan walau PHP server tua.
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

echo "==============================================\n";
echo " DIAGNOSIS SERVER - SANS FAMILY\n";
echo "==============================================\n\n";

// --- 1. Versi PHP ---
$phpOk = version_compare(PHP_VERSION, '8.2.0', '>=');
echo "[1] Versi PHP : ".PHP_VERSION.'  '.($phpOk ? '(OK)' : "(MASALAH! butuh 8.2 atau lebih baru)\n    >> cPanel -> Select PHP Version / MultiPHP Manager -> pilih PHP 8.3")."\n\n";

// --- 2. Ekstensi ---
echo "[2] Ekstensi PHP:\n";
$butuh = array('pdo_sqlite', 'sqlite3', 'gd', 'mbstring', 'fileinfo', 'openssl', 'curl', 'zip', 'ctype', 'dom', 'session', 'tokenizer');
$extKurang = array();
foreach ($butuh as $ext) {
    $ada = extension_loaded($ext);
    if (! $ada) { $extKurang[] = $ext; }
    echo '    '.str_pad($ext, 12).($ada ? 'OK' : 'TIDAK ADA  << centang di Select PHP Version')."\n";
}
echo "\n";

// --- 3. Lokasi aplikasi ---
$kandidat = array(
    dirname(__DIR__),
    dirname(__DIR__).'/sans-family',
    __DIR__.'/sans-family',
);
$appDir = null;
foreach ($kandidat as $dir) {
    if (@is_file($dir.'/vendor/autoload.php')) { $appDir = $dir; break; }
}

echo "[3] Folder aplikasi: ";
if ($appDir === null) {
    echo "TIDAK KETEMU!\n";
    echo "    File ini harus berada di folder `public` milik aplikasi\n";
    echo "    (yang bersebelahan dengan folder app/, vendor/, storage/).\n";
    echo "    Lokasi file ini sekarang: ".__DIR__."\n\n";
} else {
    echo $appDir." (OK)\n\n";

    // --- 4. File penting ---
    echo "[4] File penting:\n";
    $cek = array(
        '.env' => '.env',
        'vendor/autoload.php' => 'vendor (composer)',
        'database/database.sqlite' => 'database SQLite',
        'public/index.php' => 'public/index.php',
    );
    foreach ($cek as $rel => $label) {
        echo '    '.str_pad($label, 20).(@file_exists($appDir.'/'.$rel) ? 'OK' : 'TIDAK ADA!')."\n";
    }
    echo "\n";

    // --- 5. Izin tulis ---
    echo "[5] Izin tulis (harus BISA):\n";
    $tulis = array('storage', 'storage/framework', 'storage/framework/views', 'storage/logs', 'bootstrap/cache', 'database');
    foreach ($tulis as $rel) {
        $p = $appDir.'/'.$rel;
        echo '    '.str_pad($rel, 26).(@is_writable($p) ? 'BISA' : 'TIDAK BISA  << set permission 755 (File Manager -> Permissions)')."\n";
    }
    echo "\n";

    // --- 6. Symlink storage ---
    $link = $appDir.'/public/storage';
    echo "[6] Symlink foto  : ".((@is_link($link) || @is_dir($link)) ? 'OK' : 'BELUM (jalankan storage:link / setup-storage-link.php)')."\n\n";

    // --- 7. Ekor log Laravel ---
    $log = $appDir.'/storage/logs/laravel.log';
    echo "[7] Log Laravel (40 baris terakhir):\n";
    if (@is_file($log)) {
        $baris = @file($log);
        if ($baris) {
            $ambil = array_slice($baris, -40);
            echo '    ...'."\n";
            foreach ($ambil as $b) { echo '    '.rtrim($b)."\n"; }
        } else {
            echo "    (log ada tapi tidak terbaca)\n";
        }
    } else {
        echo "    (belum ada log - berarti Laravel belum pernah berhasil boot;\n";
        echo "     hampir pasti masalahnya di [1] versi PHP atau [2] ekstensi)\n";
    }
}

echo "\n==============================================\n";
echo " SELESAI. Screenshot hasil ini, lalu HAPUS\n";
echo " file cek-server.php dari server!\n";
echo "==============================================\n";
