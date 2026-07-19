{{-- $child — kartu peliharaan yang tumbuh dengan poin --}}
@php($pet = $child->petProgress())
<section class="card pet-card" id="pet-card" style="--child: {{ $child->color }}" data-pet-stage="{{ $pet['stage'] }}">
    <div class="pet-emoji" id="pet-emoji" aria-hidden="true">{{ $pet['emoji'] }}</div>
    <div class="pet-info">
        <b class="pet-name">{{ $pet['species'] }} · {{ $pet['stage_name'] }} <span class="chip">Lv {{ $pet['level'] }}</span></b>
        @if ($pet['is_max'])
            <span class="muted">🌟 Peliharaanmu sudah maksimal! Keren banget!</span>
        @else
            <span class="bar bar-mini"><span class="bar-fill" id="pet-bar" style="width: {{ $pet['percent'] }}%"></span></span>
            <span class="muted pet-next">Kumpulkan <b id="pet-tonext">{{ number_format($pet['to_next'], 0, ',', '.') }}</b> poin lagi untuk naik level!</span>
        @endif
    </div>
</section>
