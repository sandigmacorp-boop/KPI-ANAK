# ============================================================
#  SANS FAMILY - Pembuat paket upload hosting
#  Hasil: sans-family-hosting.zip di folder project
#
#  Pakai:   klik dua kali buat-paket-hosting.bat  (di folder project)
#  Opsi :   -TanpaData   -> tanpa database & foto (mulai dari nol di server)
# ============================================================
param([switch]$TanpaData)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$staging = Join-Path $env:TEMP ("sans-family-paket-" + (Get-Date -Format 'yyyyMMdd-HHmmss'))
$zipOut = Join-Path $root 'sans-family-hosting.zip'

Write-Host ""
Write-Host "  Menyiapkan paket hosting SANS FAMILY..." -ForegroundColor Cyan
Write-Host "  Sumber : $root"

# 1. Salin project ke folder kerja, tanpa file yang tidak perlu di server
$exDirs = @((Join-Path $root '.git'), (Join-Path $root '.claude'), (Join-Path $root 'node_modules'), (Join-Path $root 'tests'), (Join-Path $root 'public\storage'))
robocopy $root $staging /E /XJ /NFL /NDL /NJH /NJS /XD @exDirs /XF (Join-Path $root '.env') 'sans-family-hosting.zip' '*.log' | Out-Null
if ($LASTEXITCODE -ge 8) { throw "Robocopy gagal (kode $LASTEXITCODE)" }

# 2. Buang cache runtime hasil development (akan dibuat ulang oleh server)
Get-ChildItem "$staging\bootstrap\cache" -Filter '*.php' -File -ErrorAction SilentlyContinue | Remove-Item -Force
foreach ($d in 'views', 'sessions', 'cache\data', 'testing') {
    $p = "$staging\storage\framework\$d"
    if (Test-Path $p) { Get-ChildItem $p -Recurse -File | Where-Object Name -ne '.gitignore' | Remove-Item -Force }
}

# 3. Data ikut atau tidak
if ($TanpaData) {
    Remove-Item "$staging\database\database.sqlite" -Force -ErrorAction SilentlyContinue
    if (Test-Path "$staging\storage\app\public\bukti") { Remove-Item "$staging\storage\app\public\bukti" -Recurse -Force }
    Write-Host "  Data    : TIDAK disertakan (jalankan 'php artisan migrate --seed' di server)" -ForegroundColor Yellow
} else {
    Write-Host "  Data    : database + foto bukti saat ini ikut dibawa" -ForegroundColor Green
}

# 4. Buat .env produksi dengan APP_KEY baru
Push-Location $root
$appKey = (php artisan key:generate --show).Trim()
Pop-Location
if (-not $appKey.StartsWith('base64:')) { throw "Gagal membuat APP_KEY" }

$envProd = @"
APP_NAME="SANS FAMILY"
APP_ENV=production
APP_KEY=$appKey
APP_DEBUG=false

# >>> GANTI dengan alamat domain Anda <<<
APP_URL=https://GANTI-DOMAIN-ANDA.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

# SQLite: tanpa setup database apa pun (file database/database.sqlite).
# Bila ingin MySQL, lihat bagian "Memakai MySQL" di DEPLOY.md.
DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="halo@sansfamily.local"
MAIL_FROM_NAME="`${APP_NAME}"
"@

Set-Content -Path "$staging\.env" -Value $envProd -Encoding ascii

# 5. Zip
if (Test-Path $zipOut) { Remove-Item $zipOut -Force }
Compress-Archive -Path "$staging\*" -DestinationPath $zipOut -CompressionLevel Optimal
Remove-Item $staging -Recurse -Force

$sizeMb = [math]::Round((Get-Item $zipOut).Length / 1MB, 1)
Write-Host ""
Write-Host "  SELESAI (OK)  $zipOut  ($sizeMb MB)" -ForegroundColor Green
Write-Host ""
Write-Host "  Langkah berikutnya (detail di DEPLOY.md):"
Write-Host "   1. Upload zip ini ke hosting, ekstrak ke folder 'sans-family' (BUKAN public_html)."
Write-Host "   2. Arahkan document root domain ke folder 'sans-family/public'."
Write-Host "   3. Edit file .env di server -> isi APP_URL dengan domain Anda."
Write-Host "   4. Jalankan 'php artisan storage:link' di Terminal cPanel"
Write-Host "      (atau pakai deploy/setup-storage-link.php bila tidak ada Terminal)."
Write-Host "   5. Aktifkan SSL (AutoSSL/Let's Encrypt), lalu login & GANTI KATA SANDI."
Write-Host ""
if (-not $TanpaData) {
    Write-Host "  PENTING: paket ini membawa akun & data saat ini." -ForegroundColor Yellow
    Write-Host "  Segera ganti kata sandi begitu situs online!" -ForegroundColor Yellow
}
