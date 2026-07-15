<?php

namespace App\Support;

use App\Models\Child;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Simpan & hapus foto bukti tugas (di-resize agar hemat ruang). */
class ProofPhoto
{
    public const MAX_SIDE = 1280;

    public static function store(UploadedFile $file, Child $child): ?string
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
            $img = match ($exif['Orientation'] ?? 1) {
                3 => imagerotate($img, 180, 0),
                6 => imagerotate($img, -90, 0),
                8 => imagerotate($img, 90, 0),
                default => $img,
            };
        }

        // Kecilkan bila lebih besar dari MAX_SIDE.
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::MAX_SIDE / max($w, $h));

        if ($scale < 1) {
            $img = imagescale($img, (int) round($w * $scale), (int) round($h * $scale));
        }

        // Ratakan ke latar putih (untuk PNG/WebP transparan) lalu simpan sebagai JPEG.
        $flat = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));

        ob_start();
        imagejpeg($flat, null, 82);
        $jpeg = ob_get_clean();

        imagedestroy($img);
        imagedestroy($flat);

        $path = 'bukti/'.$child->id.'/'.now()->format('Ymd').'-'.Str::random(20).'.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
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
