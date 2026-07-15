{{-- $week: hasil dailyStats 7 hari (dipakai laporan orang tua & performa anak) --}}
@php($maxPct = collect($week)->max('percent'))
<div class="chart">
    <div class="bars" role="img" aria-label="Grafik KPI harian tujuh hari terakhir">
        <div class="target" style="--t: {{ \App\Models\Child::STREAK_MIN }}%">
            <span>target {{ \App\Models\Child::STREAK_MIN }}</span>
        </div>
        @foreach ($week as $d)
            @php($p = $d['percent'])
            <div class="bar-col" style="--h: {{ $p ?? 0 }}%"
                 title="{{ $d['date']->translatedFormat('l, j F') }}: {{ $p === null ? 'tidak ada tugas' : $p.'% ('.$d['done_tasks'].'/'.$d['total_tasks'].' tugas)' }}">
                @if ($p !== null && ($d['date']->isToday() || ($p === $maxPct && $p > 0)))
                    <span class="bar-val">{{ $p }}</span>
                @endif
                <div class="bar-mark {{ $d['date']->isToday() ? 'today' : '' }} {{ $p === null ? 'none' : '' }}"></div>
            </div>
        @endforeach
    </div>
    <div class="bar-labels">
        @foreach ($week as $d)
            <span class="{{ $d['date']->isToday() ? 'today' : '' }}">{{ $d['date']->translatedFormat('D') }}</span>
        @endforeach
    </div>
</div>
<details class="numbers">
    <summary>Lihat angka</summary>
    <table class="table">
        <thead><tr><th>Tanggal</th><th>KPI</th><th>Tugas</th></tr></thead>
        <tbody>
        @foreach ($week as $d)
            <tr>
                <td>{{ $d['date']->translatedFormat('D, j M') }}</td>
                <td>{{ $d['percent'] === null ? '—' : $d['percent'].'%' }}</td>
                <td>{{ $d['done_tasks'] }}/{{ $d['total_tasks'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</details>
