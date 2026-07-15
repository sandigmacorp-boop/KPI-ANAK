<?php

namespace App\Support;

use App\Models\Child;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Simpan & hapus foto bukti tugas.
 * Bila ekstensi GD tersedia, foto dipotong-tengah jadi kotak, diperkecil ke
 * 400x400, dan disimpan sebagai WebP (~15-30 KB) agar sangat hemat ruang.
 * Bila GD TIDAK tersedia di server, berkas disimpan apa adanya (klien sudah
 * memperkecil ~150 KB) supaya upload tetap berhasil — tidak error 500.
 */
class ProofPhoto
{
    /** Sisi keluaran (kotak). */
    public const SIZE = 400;

    /** Kualitas WebP/JPEG (0-100). */
    public const QUALITY = 80;

    public static function store(UploadedFile $file, Child $child): ?string
    {
        $processed = function_exists('imagecreatefromstring') ? self::toSquareImage($file) : null;

        if ($processed !== null) {
            [$data, $ext] = $processed;
        } else {
            // Cadangan tanpa GD: simpan berkas asli (aman & tetap kecil).
            $data = @file_get_contents($file->getRealPath());
            if ($data === false || $data === '') {
                return null;
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $ext = 'jpg';
            }
        }

        $path = 'bukti/'.$child->id.'/'.now()->format('Ymd').'-'.Str::random(20).'.'.$ext;
        Storage::disk('public')->put($path, $data);

        return $path;
    }

    /**
     * Olah foto dengan GD: rotasi EXIF → potong-tengah kotak → 400x400 → WebP.
     *
     * @return array{0: string, 1: string}|null  [byte gambar, ekstensi] atau null bila gagal
     */
    private static function toSquareImage(UploadedFile $file): ?array
    {
        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false) {
            return null;
        }

        $img = @imagecreatefromstring($raw);
        if ($img === false) {
            return null;
        }

        // Putar sesuai EXIF bila tersedia (foto kamera HP).
        if (function_exists('exif_read_data') && in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'])) {
            $exif = @exif_read_data($file->getRealPath());
            $rotated = match ($exif['Orientation'] ?? 1) {
                3 => imagerotate($img, 180, 0),
                6 => imagerotate($img, -90, 0),
                8 => imagerotate($img, 90, 0),
                default => null,
            };
            if ($rotated !== false && $rotated !== null) {
                imagedestroy($img);
                $img = $rotated;
            }
        }

        // Potong bagian tengah menjadi kotak, lalu skala ke SIZE x SIZE.
        $w = imagesx($img);
        $h = imagesy($img);
        $side = min($w, $h);
        $srcX = intdiv($w - $side, 2);
        $srcY = intdiv($h - $side, 2);

        $dst = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $img, 0, 0, $srcX, $srcY, self::SIZE, self::SIZE, $side, $side);

        // Utamakan WebP; bila GD server tanpa dukungan WebP, jatuh ke JPEG.
        $useWebp = function_exists('imagewebp');

        ob_start();
        if ($useWebp) {
            imagewebp($dst, null, self::QUALITY);
        } else {
            imagejpeg($dst, null, self::QUALITY);
        }
        $data = ob_get_clean();

        imagedestroy($img);
        imagedestroy($dst);

        if ($data === false || $data === '') {
            return null;
        }

        return [$data, $useWebp ? 'webp' : 'jpg'];
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        // Relatif terhadap origin — tetap benar diakses dari localhost maupun IP WiFi.
        return $path ? '/storage/'.$path : null;
    }
}
