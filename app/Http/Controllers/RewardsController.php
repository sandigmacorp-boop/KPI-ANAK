<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Redemption;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    public function index(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);

        return view('kelola.hadiah', [
            'child' => $child,
            'rewards' => $child->rewards()->orderBy('cost')->get(),
            'pending' => $child->redemptions()->pending()->orderBy('created_at')->get(),
            'history' => $child->redemptions()
                ->where(fn ($q) => $q->whereNotNull('delivered_at')->orWhereNotNull('canceled_at'))
                ->latest()->limit(30)->get(),
            'breakdown' => $child->pointsBreakdown(),
        ]);
    }

    public function store(Request $request, Child $child)
    {
        $this->authorizeChild($request, $child);
        $child->rewards()->create($this->validated($request));

        return redirect()->route('rewards.index', $child)->with('ok', 'Hadiah ditambahkan ke katalog.');
    }

    public function update(Request $request, Reward $reward)
    {
        $this->authorizeReward($request, $reward);
        $reward->update($this->validated($request));

        return redirect()->route('rewards.index', $reward->child)->with('ok', 'Hadiah diperbarui.');
    }

    public function toggleActive(Request $request, Reward $reward)
    {
        $this->authorizeReward($request, $reward);
        $reward->update(['is_active' => ! $reward->is_active]);

        return redirect()->route('rewards.index', $reward->child)
            ->with('ok', $reward->is_active ? 'Hadiah ditampilkan lagi di katalog anak.' : 'Hadiah disembunyikan dari katalog anak.');
    }

    public function destroy(Request $request, Reward $reward)
    {
        $this->authorizeReward($request, $reward);
        $child = $reward->child;
        $reward->delete(); // riwayat penukaran tetap aman (snapshot)

        return redirect()->route('rewards.index', $child)->with('ok', 'Hadiah dihapus dari katalog.');
    }

    /** Tandai penukaran sudah diserahkan ke anak. */
    public function deliver(Request $request, Redemption $redemption)
    {
        $this->authorizeRedemption($request, $redemption);
        abort_unless($redemption->isPending(), 400);

        $redemption->update(['delivered_at' => now()]);

        return redirect()->route('rewards.index', $redemption->child)->with('ok', 'Hadiah ditandai sudah diberikan. 🎉');
    }

    /** Batalkan penukaran — poin otomatis kembali ke saldo anak. */
    public function cancel(Request $request, Redemption $redemption)
    {
        $this->authorizeRedemption($request, $redemption);
        abort_unless($redemption->isPending(), 400);

        $redemption->update(['canceled_at' => now()]);

        return redirect()->route('rewards.index', $redemption->child)
            ->with('ok', "Penukaran dibatalkan — {$redemption->cost} poin dikembalikan ke {$redemption->child->name}.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'cost' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $data['emoji'] = filled($data['emoji'] ?? null) ? $data['emoji'] : '🎁';

        return $data;
    }

    private function authorizeChild(Request $request, Child $child): void
    {
        abort_unless($child->user_id === $request->user()->id, 403);
    }

    private function authorizeReward(Request $request, Reward $reward): void
    {
        abort_unless($reward->child->user_id === $request->user()->id, 403);
    }

    private function authorizeRedemption(Request $request, Redemption $redemption): void
    {
        abort_unless($redemption->child->user_id === $request->user()->id, 403);
    }
}
