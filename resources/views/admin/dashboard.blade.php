@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', '🛡️ Dashboard Admin')

@section('content')
    <p class="date-line">Monitor seluruh keluarga & anak lintas-tenant — hanya terlihat oleh admin.</p>

    <a href="{{ route('admin.email.edit') }}" class="btn btn-ghost btn-block">📧 Pengaturan Email (Resend)</a>

    {{-- Ringkasan angka --}}
    <div class="stat-grid">
        <div class="card stat">
            <span class="stat-num">👨‍👩‍👧 {{ number_format($stats['households'], 0, ',', '.') }}</span>
            <span class="stat-cap">Total Keluarga</span>
        </div>
        <div class="card stat">
            <span class="stat-num">🧑 {{ number_format($stats['parents'], 0, ',', '.') }}</span>
            <span class="stat-cap">Total Orang Tua</span>
        </div>
        <div class="card stat">
            <span class="stat-num">🧒 {{ number_format($stats['children'], 0, ',', '.') }}</span>
            <span class="stat-cap">Total Anak</span>
        </div>
        <div class="card stat">
            <span class="stat-num">🆕 {{ number_format($stats['new_households_7d'], 0, ',', '.') }}</span>
            <span class="stat-cap">Keluarga Baru (7 hari)</span>
        </div>
    </div>

    {{-- Kesehatan sistem --}}
    <section class="card">
        <h3 class="card-title">💚 Kesehatan Sistem</h3>
        <table class="table">
            <tbody>
                <tr>
                    <td>📨 Orang tua terhubung Telegram</td>
                    <td class="num">{{ $health['telegram_linked'] }}/{{ $health['telegram_total'] }}</td>
                </tr>
                <tr>
                    <td>📷 Foto bukti tersimpan</td>
                    <td class="num">{{ number_format($health['photo_count'], 0, ',', '.') }} file ({{ $health['photo_size_mb'] }} MB)</td>
                </tr>
                <tr>
                    <td>🗄️ Ukuran database</td>
                    <td class="num">{{ $health['is_sqlite'] ? $health['db_size_mb'].' MB (SQLite)' : 'MySQL (lihat panel hosting)' }}</td>
                </tr>
                <tr>
                    <td>⚙️ Versi</td>
                    <td class="num">PHP {{ $health['php_version'] }} · Laravel {{ $health['laravel_version'] }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    {{-- Aktivitas terbaru --}}
    <section class="card">
        <h3 class="card-title">🔔 Aktivitas Terbaru</h3>
        @if ($activity->isEmpty())
            <p class="muted">Belum ada aktivitas.</p>
        @else
            <div class="admin-feed">
                @foreach ($activity as $ev)
                    <div class="admin-feed-row">
                        <span class="admin-feed-icon" aria-hidden="true">{{ $ev['icon'] }}</span>
                        <span class="admin-feed-text">{!! $ev['text'] !!}</span>
                        <span class="admin-feed-time muted">{{ $ev['at']->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Daftar semua keluarga --}}
    <section class="card">
        <h3 class="card-title">👨‍👩‍👧 Semua Keluarga <small class="muted">({{ $households->total() }})</small></h3>
        @foreach ($households as $h)
            <div class="admin-household-row">
                <div class="admin-household-main">
                    <b>{{ $h->name }}</b>
                    <span class="muted">
                        {{ $h->users->pluck('name')->implode(', ') ?: '—' }}
                        @if ($h->users->isNotEmpty())
                            · {{ $h->users->pluck('email')->implode(', ') }}
                        @endif
                    </span>
                    <span class="muted">
                        🧒 {{ $h->children_count }} anak
                        @if ($h->children->isNotEmpty())
                            ({{ $h->children->pluck('name')->implode(', ') }})
                        @endif
                        · daftar {{ $h->created_at->translatedFormat('j M Y') }}
                    </span>
                </div>
                <span class="admin-household-active">
                    @if ($h->isDisabled())
                        <span class="chip chip-danger">⛔ nonaktif</span>
                    @elseif ($h->last_active_at)
                        <span class="chip chip-done">{{ $h->last_active_at->diffForHumans() }}</span>
                    @else
                        <span class="chip chip-muted">belum ada aktivitas</span>
                    @endif

                    @unless ($h->id === auth()->user()->household_id)
                        <form method="post" action="{{ route('admin.household.toggle', $h) }}"
                              data-confirm="{{ $h->isDisabled()
                                  ? 'Aktifkan kembali keluarga '.$h->name.'?'
                                  : 'Nonaktifkan keluarga '.$h->name.'? Semua anggotanya langsung logout & tak bisa akses lagi.' }}">
                            @csrf
                            <button class="btn btn-sm {{ $h->isDisabled() ? 'btn-primary' : 'btn-danger' }}">
                                {{ $h->isDisabled() ? '✅ Aktifkan' : '⛔ Nonaktifkan' }}
                            </button>
                        </form>
                    @endunless
                </span>
            </div>
        @endforeach

        <div class="admin-pagination">{{ $households->links() }}</div>
    </section>
@endsection
