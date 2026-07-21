<?php

namespace App\Models;

use App\Support\ProofPhoto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

/** Misi kerja sama tim: satu laporan mewakili seluruh anak; disetujui orang tua memberi bonus ke semua anak. */
#[Fillable(['household_id', 'title', 'emoji', 'description', 'points', 'status', 'completed_at'])]
class TeamChallenge extends Model
{
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TeamChallengeSubmission::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** Laporan yang sedang menunggu persetujuan, bila ada. */
    public function pendingSubmission(): ?TeamChallengeSubmission
    {
        return $this->relationLoaded('submissions')
            ? $this->submissions->firstWhere('status', 'pending')
            : $this->submissions()->where('status', 'pending')->first();
    }

    /**
     * Anak mengirim laporan (foto + catatan). Misi berpindah ke status "pending".
     *
     * @param  UploadedFile[]  $photos
     */
    public function submitReport(Child $child, array $photos, ?string $note): TeamChallengeSubmission
    {
        $submission = $this->submissions()->create([
            'child_id' => $child->id,
            'note' => $note,
            'status' => 'pending',
        ]);

        foreach ($photos as $photo) {
            $path = ProofPhoto::store($photo, $child);
            if ($path) {
                $submission->photos()->create(['photo_path' => $path]);
            }
        }

        $this->update(['status' => 'pending']);

        return $submission;
    }
}
