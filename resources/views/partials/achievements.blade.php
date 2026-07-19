{{-- $child — grid lencana (diraih & terkunci dengan progres) --}}
@php($ach = $child->achievementsProgress())
@php($earnedCount = collect($ach)->where('earned', true)->count())
<section class="card">
    <h3 class="card-title">🏅 Lencana <small class="muted">{{ $earnedCount }}/{{ count($ach) }}</small></h3>
    <div class="badge-grid">
        @foreach ($ach as $a)
            <div class="badge {{ $a['earned'] ? 'earned' : 'locked' }}" title="{{ $a['desc'] }}">
                <span class="badge-emoji">{{ $a['emoji'] }}</span>
                <span class="badge-title">{{ $a['title'] }}</span>
                @if ($a['earned'])
                    <span class="badge-sub">✓ {{ $a['earned_at']?->translatedFormat('j M') }}</span>
                @else
                    <span class="bar bar-mini"><span class="bar-fill" style="width: {{ $a['percent'] }}%"></span></span>
                    <span class="badge-sub">{{ $a['value'] }}/{{ $a['threshold'] }}</span>
                @endif
            </div>
        @endforeach
    </div>
</section>
