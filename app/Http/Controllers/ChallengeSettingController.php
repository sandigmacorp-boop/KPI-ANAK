<?php

namespace App\Http\Controllers;

use App\Support\WeeklyChallenge;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChallengeSettingController extends Controller
{
    /** Tetapkan tantangan pekan ini: pilih preset atau buat custom. */
    public function store(Request $request)
    {
        $presetKeys = array_column(WeeklyChallenge::LIST, 'key');

        $data = $request->validate([
            'choice' => ['required', Rule::in([...$presetKeys, 'custom'])],
            'title' => ['nullable', 'required_if:choice,custom', 'string', 'max:60'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'metric' => ['nullable', 'required_if:choice,custom', Rule::in(array_keys(WeeklyChallenge::METRICS))],
            'target' => ['nullable', 'required_if:choice,custom', 'integer', 'min:1', 'max:10000'],
            'bonus' => ['nullable', 'required_if:choice,custom', 'integer', 'min:1', 'max:1000'],
        ], [
            'title.required_if' => 'Judul wajib diisi untuk tantangan custom.',
            'metric.required_if' => 'Pilih ukuran tantangan custom.',
            'target.required_if' => 'Target wajib diisi untuk tantangan custom.',
            'bonus.required_if' => 'Bonus poin wajib diisi untuk tantangan custom.',
        ]);

        if ($data['choice'] === 'custom') {
            $fields = [
                'challenge_key' => 'custom',
                'emoji' => filled($data['emoji'] ?? null) ? $data['emoji'] : '🎯',
                'title' => $data['title'],
                'desc' => 'Capai '.$data['target'].' — '.strtolower(WeeklyChallenge::METRICS[$data['metric']]),
                'metric' => $data['metric'],
                'target' => $data['target'],
                'bonus' => $data['bonus'],
            ];
        } else {
            $preset = WeeklyChallenge::preset($data['choice']);
            $fields = [
                'challenge_key' => $preset['key'],
                'emoji' => $preset['emoji'],
                'title' => $preset['title'],
                'desc' => $preset['desc'],
                'metric' => $preset['metric'],
                'target' => $preset['target'],
                'bonus' => $preset['bonus'],
            ];
        }

        $request->user()->household->challengeSettings()->updateOrCreate(
            ['week_key' => WeeklyChallenge::weekKey()],
            $fields,
        );

        return redirect()->route('children.index')->with('ok', 'Tantangan pekan ini diperbarui: '.$fields['title']);
    }

    /** Kembalikan ke rotasi otomatis untuk pekan ini. */
    public function reset(Request $request)
    {
        $request->user()->household->challengeSettings()
            ->where('week_key', WeeklyChallenge::weekKey())->delete();

        return redirect()->route('children.index')->with('ok', 'Tantangan pekan ini kembali ke rotasi otomatis.');
    }
}
