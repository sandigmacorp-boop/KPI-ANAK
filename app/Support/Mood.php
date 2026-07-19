<?php

namespace App\Support;

/** Katalog perasaan harian anak. */
class Mood
{
    public const MOODS = [
        'senang' => ['emoji' => '😄', 'label' => 'Senang'],
        'semangat' => ['emoji' => '🤩', 'label' => 'Semangat'],
        'biasa' => ['emoji' => '🙂', 'label' => 'Biasa'],
        'lelah' => ['emoji' => '😴', 'label' => 'Lelah'],
        'sedih' => ['emoji' => '😢', 'label' => 'Sedih'],
        'marah' => ['emoji' => '😠', 'label' => 'Kesal'],
    ];

    public static function emoji(?string $key): string
    {
        return self::MOODS[$key]['emoji'] ?? '·';
    }

    public static function label(?string $key): string
    {
        return self::MOODS[$key]['label'] ?? '';
    }
}
