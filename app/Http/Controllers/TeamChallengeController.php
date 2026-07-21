<?php

namespace App\Http\Controllers;

use App\Models\TeamChallenge;
use App\Models\TeamChallengeSubmission;
use Illuminate\Http\Request;

class TeamChallengeController extends Controller
{
    public function index(Request $request)
    {
        $household = $request->user()->household;

        $challenges = $household->teamChallenges()
            ->with(['submissions' => fn ($q) => $q->latest(), 'submissions.photos', 'submissions.child'])
            ->latest()->get();

        return view('kelola.tim', [
            'children' => $household->children()->orderBy('created_at')->get(),
            'active' => $challenges->whereIn('status', ['open', 'pending'])->values(),
            'completed' => $challenges->where('status', 'approved')->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $request->user()->household->teamChallenges()->create($data + ['status' => 'open']);

        return redirect()->route('team.index')->with('ok', 'Tantangan tim ditambahkan! 🤝');
    }

    public function update(Request $request, TeamChallenge $challenge)
    {
        $this->authorizeChallenge($request, $challenge);
        abort_unless($challenge->isOpen(), 400, 'Tantangan yang sedang berjalan tidak bisa diubah.');

        $challenge->update($this->validated($request));

        return redirect()->route('team.index')->with('ok', 'Tantangan tim diperbarui.');
    }

    public function destroy(Request $request, TeamChallenge $challenge)
    {
        $this->authorizeChallenge($request, $challenge);

        foreach ($challenge->submissions as $submission) {
            $submission->deletePhotoFiles();
        }
        $challenge->delete();

        return redirect()->route('team.index')->with('ok', 'Tantangan tim dihapus.');
    }

    public function approve(Request $request, TeamChallengeSubmission $submission)
    {
        $this->authorizeSubmission($request, $submission);
        abort_unless($submission->isPending(), 400);

        $submission->approve();
        $count = $submission->teamChallenge->household->children()->count();

        return redirect()->route('team.index')
            ->with('ok', "🎉 Disetujui! +{$submission->teamChallenge->points} poin diberikan ke {$count} anak.");
    }

    public function reject(Request $request, TeamChallengeSubmission $submission)
    {
        $this->authorizeSubmission($request, $submission);
        abort_unless($submission->isPending(), 400);

        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:200']]);
        $submission->reject($data['review_note'] ?? null);

        return redirect()->route('team.index')->with('ok', 'Laporan ditolak — tim bisa mengirim laporan baru.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:200'],
            'points' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $data['emoji'] = filled($data['emoji'] ?? null) ? $data['emoji'] : '🤝';

        return $data;
    }

    private function authorizeChallenge(Request $request, TeamChallenge $challenge): void
    {
        abort_unless($challenge->household_id === $request->user()->household_id, 403);
    }

    private function authorizeSubmission(Request $request, TeamChallengeSubmission $submission): void
    {
        abort_unless($submission->teamChallenge->household_id === $request->user()->household_id, 403);
    }
}
