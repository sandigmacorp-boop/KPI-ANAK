{{-- $b: hasil Child::pointsBreakdown() — rincian saldo ringkas --}}
<span class="muted">
    dari tugas {{ number_format($b['from_tasks'], 0, ',', '.') }}
    @if ($b['bonus'] > 0)
        · bonus +{{ number_format($b['bonus'], 0, ',', '.') }}
    @endif
    @if ($b['penalty'] < 0)
        · pelanggaran −{{ number_format(abs($b['penalty']), 0, ',', '.') }}
    @endif
    @if ($b['spent'] > 0)
        · ditukar −{{ number_format($b['spent'], 0, ',', '.') }}
    @endif
</span>
