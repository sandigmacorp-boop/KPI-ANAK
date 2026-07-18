<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/** Klien ringan Telegram Bot API (tanpa webhook — cukup token bot). */
class Telegram
{
    public static function token(): ?string
    {
        return config('services.telegram.token');
    }

    public static function enabled(): bool
    {
        return filled(self::token());
    }

    /** Panggil sebuah method Bot API; kembalikan array hasil atau null bila gagal. */
    public static function api(string $method, array $params = []): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        try {
            $res = Http::timeout(15)->asForm()
                ->post('https://api.telegram.org/bot'.self::token()."/{$method}", $params);
            $json = $res->json();

            return is_array($json) ? $json : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function send(string|int $chatId, string $text): bool
    {
        $res = self::api('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ]);

        return (bool) ($res['ok'] ?? false);
    }

    /** Info bot (mis. username) atau null. */
    public static function getMe(): ?array
    {
        $res = self::api('getMe');

        return ($res['ok'] ?? false) ? ($res['result'] ?? null) : null;
    }

    /** @return array<int, array> daftar update terbaru (tanpa webhook). */
    public static function getUpdates(): array
    {
        $res = self::api('getUpdates', ['timeout' => 0, 'allowed_updates' => json_encode(['message'])]);

        return ($res['ok'] ?? false) ? ($res['result'] ?? []) : [];
    }

    /**
     * Cari chat_id dari pesan "/start {code}" (atau "{code}") pada update terbaru.
     * Dipakai untuk menghubungkan akun tanpa webhook.
     */
    public static function findChatIdForCode(string $code): ?int
    {
        foreach (self::getUpdates() as $update) {
            $msg = $update['message'] ?? null;
            if (! $msg) {
                continue;
            }
            $text = trim($msg['text'] ?? '');
            if ($text === "/start {$code}" || $text === $code) {
                return isset($msg['chat']['id']) ? (int) $msg['chat']['id'] : null;
            }
        }

        return null;
    }
}
