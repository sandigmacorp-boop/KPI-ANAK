<?php

namespace App\Models;

use App\Support\ProofPhoto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_challenge_id', 'child_id', 'note', 'status', 'review_note', 'reviewed_at'])]
class TeamChallengeSubmission extends Model
{
    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function teamChallenge(): BelongsTo
    {
        return $this->belongsTo(TeamChallenge::class);
    }

    /** Anak yang mengirim laporan (mewakili tim). */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TeamChallengePhoto::class, 'submission_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** Setujui: kunci misi selesai + beri bonus poin ke SEMUA anak dalam keluarga. */
    public function approve(): void
    {
        $challenge = $this->teamChallenge;

        $this->update(['status' => 'approved', 'reviewed_at' => now()]);
        $challenge->update(['status' => 'approved', 'completed_at' => now()]);

        foreach ($challenge->household->children as $child) {
            $child->adjustments()->create([
                'amount' => $challenge->points,
                'reason' => "🤝 Tantangan Tim: {$challenge->title}",
            ]);
        }
    }

    /** Tolak: misi kembali terbuka agar anak bisa mengirim laporan baru. */
    public function reject(?string $reason): void
    {
        $this->update(['status' => 'rejected', 'review_note' => $reason, 'reviewed_at' => now()]);
        $this->teamChallenge->update(['status' => 'open']);
    }

    /** Hapus foto fisik saat submission dihapus (dipanggil dari controller sebelum delete). */
    public function deletePhotoFiles(): void
    {
        foreach ($this->photos as $photo) {
            ProofPhoto::delete($photo->photo_path);
        }
    }
}
