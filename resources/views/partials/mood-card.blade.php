{{-- $child, $day, $moodUrl (null = hanya-baca) --}}
@php($current = $child->moodFor($day))
<section class="card mood-card" style="--child: {{ $child->color }}" @if($moodUrl) data-mood-url="{{ $moodUrl }}" @endif>
    <h3 class="card-title">😊 Perasaan {{ $day->isToday() ? 'hari ini' : $day->translatedFormat('j M') }}</h3>
    @if ($moodUrl)
        <div class="mood-picker">
            @foreach (\App\Support\Mood::MOODS as $key => $m)
                <button type="button" class="mood-btn {{ $current === $key ? 'selected' : '' }}" data-mood="{{ $key }}" aria-label="{{ $m['label'] }}">
                    <span class="mood-emoji" aria-hidden="true">{{ $m['emoji'] }}</span><small>{{ $m['label'] }}</small>
                </button>
            @endforeach
        </div>
    @elseif ($current)
        <p class="mood-readonly">{{ \App\Support\Mood::emoji($current) }} {{ \App\Support\Mood::label($current) }}</p>
    @else
        <p class="muted">Belum dicatat.</p>
    @endif
</section>
