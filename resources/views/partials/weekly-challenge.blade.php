{{-- $child — tantangan mingguan yang berganti otomatis --}}
@php($wc = $child->weeklyChallengeProgress())
<section class="card challenge-card {{ $wc['completed'] ? 'challenge-done' : '' }}" id="weekly-challenge" style="--child: {{ $child->color }}">
    <div class="goal-head">
        <span class="goal-emoji" aria-hidden="true">{{ $wc['emoji'] }}</span>
        <div class="goal-body">
            <b class="goal-title">🔄 Tantangan Pekan Ini: {{ $wc['title'] }}</b>
            @if ($wc['completed'])
                <span class="chip chip-done">✅ Selesai! Bonus +{{ $wc['bonus'] }} poin</span>
            @else
                <span class="muted">{{ $wc['desc'] }} · hadiah <b>+{{ $wc['bonus'] }} poin</b></span>
            @endif
        </div>
    </div>
    <div class="bar"><div class="bar-fill" id="wc-bar" style="width: {{ $wc['percent'] }}%"></div></div>
    @unless ($wc['completed'])
        <span class="muted challenge-prog"><b id="wc-value">{{ $wc['value'] }}</b> / {{ $wc['target'] }}</span>
    @endunless
</section>
