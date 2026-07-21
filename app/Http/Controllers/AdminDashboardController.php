<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Household;
use App\Models\Redemption;
use App\Models\TaskCompletion;
use App\Models\TeamChallengeSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Dashboard pemilik platform: monitor seluruh keluarga & anak lintas-tenant (khusus admin). */
class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => $this->summaryStats(),
            'households' => $this->householdsList(),
            'activity' => $this->recentActivity(),
            'health' => $this->systemHealth(),
        ]);
    }

    /** Aktifkan/nonaktifkan sebuah keluarga. Nonaktif -> semua sesi login keluarga itu langsung diputus. */
    public function toggleHouseholdStatus(Request $request, Household $household)
    {
        abort_if($household->id === $request->user()->household_id, 422, 'Tidak bisa menonaktifkan keluarga sendiri.');

        if ($household->isDisabled()) {
            $household->forceFill(['disabled_at' => null])->save();
            $message = "Keluarga {$household->name} diaktifkan kembali.";
        } else {
            $household->forceFill(['disabled_at' => now()])->save();
            DB::table('sessions')->whereIn('user_id', $household->users()->pluck('id'))->delete();
            $message = "Keluarga {$household->name} dinonaktifkan — semua sesi login keluarga ini langsung diputus.";
        }

        return back()->with('ok', $message);
    }

    private function summaryStats(): array
    {
        return [
            'households' => Household::count(),
            'parents' => User::count(),
            'children' => Child::count(),
            'new_households_7d' => Household::whereDate('created_at', '>=', today()->subDays(6)->toDateString())->count(),
        ];
    }

    /** Daftar keluarga terbaru dulu, dengan jumlah anak & aktivitas terakhir. */
    private function householdsList(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $households = Household::with(['users:id,household_id,name,email,last_login_at', 'children:id,household_id,name,emoji'])
            ->withCount('children')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Ambil sekali: waktu tugas tercentang terbaru per anak (hindari N+1 per keluarga).
        // MAX(created_at) adalah alias SQL mentah, bukan atribut Eloquent — tidak ter-cast
        // otomatis ke Carbon, jadi harus di-parse manual (kalau tidak, perbandingan dengan
        // last_login_at yang sudah Carbon bisa salah pilih & lolos ke view sebagai string,
        // meledak saat ->diffForHumans() dipanggil di Blade).
        $lastTaskByChild = TaskCompletion::select('child_id', DB::raw('MAX(created_at) as last_at'))
            ->groupBy('child_id')
            ->pluck('last_at', 'child_id')
            ->map(fn ($value) => $value ? Carbon::parse($value) : null);

        foreach ($households as $household) {
            $lastLogin = $household->users->max('last_login_at');
            $lastTask = $household->children->pluck('id')
                ->map(fn ($id) => $lastTaskByChild[$id] ?? null)
                ->filter()
                ->max();

            $household->last_active_at = collect([$lastLogin, $lastTask])->filter()->max();
        }

        return $households;
    }

    /** Feed aktivitas lintas-keluarga: gabungan beberapa sumber, terbaru dulu. */
    private function recentActivity(int $limit = 25): \Illuminate\Support\Collection
    {
        $events = collect();

        Household::with('users:id,household_id,name')->latest()->limit(10)->get()
            ->each(function (Household $h) use ($events) {
                $events->push([
                    'at' => $h->created_at,
                    'icon' => '🆕',
                    'text' => "Keluarga baru: <b>{$h->name}</b>".($h->users->first() ? " ({$h->users->first()->name})" : ''),
                ]);
            });

        TaskCompletion::with('child:id,name,household_id', 'child.household:id,name')->latest()->limit(15)->get()
            ->each(function (TaskCompletion $c) use ($events) {
                $child = $c->child;
                $events->push([
                    'at' => $c->created_at,
                    'icon' => '✅',
                    'text' => "<b>{$child?->name}</b> ({$child?->household?->name}) menyelesaikan tugas",
                ]);
            });

        Redemption::with('child:id,name,household_id', 'child.household:id,name')->latest()->limit(10)->get()
            ->each(function (Redemption $r) use ($events) {
                $child = $r->child;
                $events->push([
                    'at' => $r->created_at,
                    'icon' => '🎁',
                    'text' => "<b>{$child?->name}</b> ({$child?->household?->name}) menukar {$r->emoji} {$r->title}",
                ]);
            });

        TeamChallengeSubmission::with('child:id,name,household_id', 'child.household:id,name', 'teamChallenge:id,title')->latest()->limit(10)->get()
            ->each(function (TeamChallengeSubmission $s) use ($events) {
                $child = $s->child;
                $events->push([
                    'at' => $s->created_at,
                    'icon' => '🤝',
                    'text' => "<b>{$child?->name}</b> ({$child?->household?->name}) kirim laporan tim: {$s->teamChallenge?->title}",
                ]);
            });

        return $events->sortByDesc('at')->take($limit)->values();
    }

    private function systemHealth(): array
    {
        $totalParents = max(User::count(), 1);
        $telegramLinked = User::whereNotNull('telegram_chat_id')->count();

        $photoFiles = Storage::disk('public')->exists('bukti') ? Storage::disk('public')->allFiles('bukti') : [];
        $photoBytes = collect($photoFiles)->sum(fn ($f) => Storage::disk('public')->size($f));

        $isSqlite = config('database.default') === 'sqlite';
        $dbBytes = $isSqlite && is_file(database_path('database.sqlite'))
            ? filesize(database_path('database.sqlite'))
            : null;

        return [
            'telegram_linked' => $telegramLinked,
            'telegram_total' => $totalParents,
            'photo_count' => count($photoFiles),
            'photo_size_mb' => round($photoBytes / 1048576, 1),
            'db_size_mb' => $dbBytes !== null ? round($dbBytes / 1048576, 1) : null,
            'is_sqlite' => $isSqlite,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }
}
