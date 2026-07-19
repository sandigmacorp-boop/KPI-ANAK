{{-- $goal: FamilyGoal|null --}}
@isset($goal)
    @if ($goal)
        @php($contrib = $goal->contributions())
        <section class="card goal-card {{ $goal->isAchieved() ? 'goal-achieved' : '' }}" id="family-goal">
            <div class="goal-head">
                <span class="goal-emoji" aria-hidden="true">{{ $goal->emoji }}</span>
                <div class="goal-body">
                    <b class="goal-title">🤝 Tujuan Keluarga: {{ $goal->title }}</b>
                    @if ($goal->isAchieved())
                        <span class="chip chip-gift">🎉 Tercapai! Ayo rayakan bersama!</span>
                    @else
                        <span class="muted"><b id="fg-progress">{{ number_format($goal->progress(), 0, ',', '.') }}</b> / {{ number_format($goal->target, 0, ',', '.') }} poin bersama</span>
                    @endif
                </div>
            </div>
            <div class="bar"><div class="bar-fill goal-bar" id="fg-bar" style="width: {{ $goal->percent() }}%"></div></div>
            <div class="goal-contrib">
                @foreach ($contrib as $c)
                    <span class="chip">{{ $c['child']->emoji }} {{ $c['child']->name }} {{ number_format($c['points'], 0, ',', '.') }}</span>
                @endforeach
            </div>
        </section>
    @endif
@endisset
