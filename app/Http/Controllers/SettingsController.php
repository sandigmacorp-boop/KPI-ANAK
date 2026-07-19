<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use App\Services\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $telegram = ['enabled' => Telegram::enabled(), 'linked' => $user->telegramLinked()];

        if ($telegram['enabled'] && ! $telegram['linked']) {
            if (blank($user->telegram_link_code)) {
                $user->update(['telegram_link_code' => Str::random(16)]);
            }
            $bot = Cache::remember('telegram_bot_username', 3600, fn () => Telegram::getMe()['username'] ?? null);
            $telegram['bot'] = $bot;
            $telegram['deep_link'] = $bot ? "https://t.me/{$bot}?start={$user->telegram_link_code}" : null;
        }

        return view('pengaturan', [
            'children' => $user->children()->orderBy('created_at')->get(),
            'parents' => $user->householdMembers()->orderBy('created_at')->get(),
            'telegram' => $telegram,
        ]);
    }

    /** Tambah orang tua kedua ke keluarga yang sama. */
    public function addParent(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'household_id' => $request->user()->household_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return redirect()->route('settings')
            ->with('ok', "Orang tua {$data['name']} ditambahkan — sekarang bisa login sendiri dengan email & sandi itu.");
    }

    public function removeParent(Request $request, User $user)
    {
        $me = $request->user();
        abort_unless($user->household_id === $me->household_id, 403);
        abort_if($user->id === $me->id, 400);

        // Pindahkan "pembuat" anak ke diri sendiri agar anak tidak ikut terhapus (cascade user_id).
        Child::where('user_id', $user->id)->update(['user_id' => $me->id]);
        $user->delete();

        return redirect()->route('settings')->with('ok', "Akun {$user->name} dihapus dari keluarga.");
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ]);

        $request->user()->update($data);

        return redirect()->route('settings')->with('ok', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('settings')->with('ok', 'Kata sandi berhasil diganti.');
    }

    /** Deteksi pesan /start di bot lalu tautkan chat_id ke akun. */
    public function linkTelegram(Request $request)
    {
        $user = $request->user();
        $code = $user->telegram_link_code;

        if (! Telegram::enabled() || blank($code)) {
            return redirect()->route('settings')->with('err', 'Telegram belum siap dikonfigurasi.');
        }

        $chatId = Telegram::findChatIdForCode($code);

        if ($chatId === null) {
            return redirect()->route('settings')
                ->with('err', 'Belum terdeteksi. Pastikan kamu sudah menekan START / mengirim pesan di bot, lalu coba lagi.');
        }

        $user->update(['telegram_chat_id' => (string) $chatId, 'telegram_link_code' => null]);
        Telegram::send($chatId, "✅ Telegram terhubung ke SANS FAMILY!\nKamu akan menerima pengingat tugas & rekap mingguan di sini.");

        return redirect()->route('settings')->with('ok', 'Telegram berhasil terhubung! 🎉');
    }

    public function testTelegram(Request $request)
    {
        $user = $request->user();

        if (! $user->telegramLinked()) {
            return redirect()->route('settings')->with('err', 'Telegram belum terhubung.');
        }

        $ok = Telegram::send($user->telegram_chat_id, '🔔 Ini pesan tes dari SANS FAMILY. Notifikasi kamu aktif!');

        return redirect()->route('settings')->with($ok ? 'ok' : 'err',
            $ok ? 'Pesan tes dikirim ke Telegram.' : 'Gagal mengirim — cek koneksi/token bot.');
    }

    public function unlinkTelegram(Request $request)
    {
        $request->user()->update(['telegram_chat_id' => null, 'telegram_link_code' => null]);

        return redirect()->route('settings')->with('ok', 'Telegram diputuskan.');
    }
}
