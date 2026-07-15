{{-- $percent: 0-100 atau null --}}
@php
    $p = $percent ?? 0;
    $c = 2 * pi() * 34;
@endphp
<div class="ring" role="img" aria-label="KPI {{ $percent === null ? 'belum ada tugas' : $percent.' persen' }}">
    <svg viewBox="0 0 80 80" aria-hidden="true">
        <circle class="ring-track" cx="40" cy="40" r="34"/>
        <circle class="ring-value" cx="40" cy="40" r="34"
                stroke-dasharray="{{ round($c, 2) }}"
                stroke-dashoffset="{{ round($c * (1 - min($p, 100) / 100), 2) }}"/>
    </svg>
    <div class="ring-label">{{ $percent === null ? '—' : $percent.'%' }}</div>
</div>
