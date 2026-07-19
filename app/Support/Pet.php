<?php

namespace App\Support;

/** Peliharaan virtual: spesies, tahap pertumbuhan, dan ambang XP. */
class Pet
{
    /** Tiap spesies punya 5 tahap pertumbuhan (emoji). */
    public const SPECIES = [
        'naga' => ['name' => 'Naga', 'stages' => ['🥚', '🐛', '🦎', '🐲', '🐉']],
        'burung' => ['name' => 'Burung', 'stages' => ['🥚', '🐣', '🐤', '🐦', '🦅']],
        'kucing' => ['name' => 'Kucing', 'stages' => ['🥚', '🐱', '🐈', '🐯', '🦁']],
        'dino' => ['name' => 'Dino', 'stages' => ['🥚', '🦎', '🐊', '🦕', '🦖']],
    ];

    /** Ambang XP (poin seumur hidup) untuk tahap 0..4. */
    public const THRESHOLDS = [0, 150, 400, 800, 1500];

    public const STAGE_NAMES = ['Telur', 'Bayi', 'Anak', 'Dewasa', 'Legendaris'];

    public static function maxStage(): int
    {
        return count(self::THRESHOLDS) - 1;
    }

    public static function species(string $type): array
    {
        return self::SPECIES[$type] ?? self::SPECIES['naga'];
    }

    public static function stageFor(int $xp): int
    {
        $stage = 0;
        foreach (self::THRESHOLDS as $i => $need) {
            if ($xp >= $need) {
                $stage = $i;
            }
        }

        return $stage;
    }

    public static function emoji(string $type, int $xp): string
    {
        return self::species($type)['stages'][self::stageFor($xp)];
    }

    /**
     * @return array{stage:int, level:int, stage_name:string, emoji:string, species:string, is_max:bool, xp:int, to_next:int, percent:int}
     */
    public static function progress(string $type, int $xp): array
    {
        $stage = self::stageFor($xp);
        $isMax = $stage >= self::maxStage();
        $floor = self::THRESHOLDS[$stage];
        $next = $isMax ? $floor : self::THRESHOLDS[$stage + 1];

        return [
            'stage' => $stage,
            'level' => $stage + 1,
            'stage_name' => self::STAGE_NAMES[$stage] ?? '',
            'emoji' => self::species($type)['stages'][$stage],
            'species' => self::species($type)['name'],
            'is_max' => $isMax,
            'xp' => $xp,
            'to_next' => $isMax ? 0 : $next - $xp,
            'percent' => $isMax ? 100 : (int) round(($xp - $floor) / max($next - $floor, 1) * 100),
        ];
    }
}
