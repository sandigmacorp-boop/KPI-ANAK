<?php

namespace App\Models;

use App\Support\ProofPhoto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['submission_id', 'photo_path'])]
class TeamChallengePhoto extends Model
{
    public function submission(): BelongsTo
    {
        return $this->belongsTo(TeamChallengeSubmission::class, 'submission_id');
    }

    public function url(): string
    {
        return ProofPhoto::url($this->photo_path);
    }
}
