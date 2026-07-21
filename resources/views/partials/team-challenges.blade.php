{{-- $child, $teamChallenges: koleksi TeamChallenge milik keluarga --}}
@php($activeList = $teamChallenges->whereIn('status', ['open', 'pending']))
@php($doneList = $teamChallenges->where('status', 'approved'))

@if ($activeList->isNotEmpty() || $doneList->isNotEmpty())
<section class="card team-section" style="--child: {{ $child->color }}">
    <h3 class="card-title">🤝 Tantangan Kerja Sama Tim</h3>

    @foreach ($activeList as $ch)
        @php($pendingSub = $ch->submissions->firstWhere('status', 'pending'))
        @php($lastRejected = $ch->submissions->where('status', 'rejected')->sortByDesc('id')->first())
        <div class="team-item {{ $ch->isPending() ? 'team-pending' : '' }}">
            <div class="goal-head">
                <span class="goal-emoji" aria-hidden="true">{{ $ch->emoji }}</span>
                <div class="goal-body">
                    <b class="goal-title">{{ $ch->title }}</b>
                    @if ($ch->description)
                        <span class="muted">{{ $ch->description }}</span>
                    @endif
                    <span class="chip">🏅 {{ $ch->points }} poin/anak bila disetujui</span>
                </div>
            </div>

            @if ($ch->isPending() && $pendingSub)
                <p class="team-status">⏳ Menunggu persetujuan Ayah/Bunda — laporan oleh {{ $pendingSub->child->name }}</p>
                @if ($pendingSub->note)
                    <p class="team-note">"{{ $pendingSub->note }}"</p>
                @endif
                <div class="team-photos">
                    @foreach ($pendingSub->photos as $p)
                        <img class="proof-thumb team-thumb" src="{{ $p->url() }}" alt="Foto laporan tim">
                    @endforeach
                </div>
            @else
                @if ($lastRejected)
                    <p class="team-rejected">❌ Laporan sebelumnya ditolak{{ $lastRejected->review_note ? ': "'.$lastRejected->review_note.'"' : '' }}. Coba lagi ya!</p>
                @endif
                <form class="team-form" data-team-url="{{ route('kid.team.submit', [$child->access_token, $ch]) }}">
                    <label class="team-upload">
                        <input type="file" name="photos" accept="image/*" multiple hidden>
                        <span class="team-upload-btn">📸 Pilih Foto (boleh lebih dari 1)</span>
                    </label>
                    <div class="team-preview"></div>
                    <input type="text" class="team-note-input" maxlength="200" placeholder="Catatan (opsional)">
                    <button type="submit" class="btn btn-primary btn-block team-submit-btn" disabled>📤 Kirim Laporan</button>
                </form>
            @endif
        </div>
    @endforeach

    @if ($doneList->isNotEmpty())
        <details class="numbers">
            <summary>Tantangan selesai ({{ $doneList->count() }})</summary>
            @foreach ($doneList as $ch)
                <div class="reward-row">
                    <span class="reward-emoji" aria-hidden="true">{{ $ch->emoji }}</span>
                    <span class="reward-body">
                        <span class="reward-title">{{ $ch->title }}</span>
                        <span class="muted">✅ {{ $ch->completed_at?->translatedFormat('j M Y') }} · +{{ $ch->points }} poin</span>
                    </span>
                </div>
            @endforeach
        </details>
    @endif
</section>
@endif
