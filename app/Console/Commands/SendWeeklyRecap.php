<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Telegram;
use App\Support\FamilyNotifier;
use Illuminate\Console\Command;

class SendWeeklyRecap extends Command
{
    protected $signature = 'sans:weekly-recap';

    protected $description = 'Kirim rekap mingguan tiap anak ke Telegram orang tua';

    public function handle(): int
    {
        if (! Telegram::enabled()) {
            $this->warn('TELEGRAM_BOT_TOKEN belum diatur — rekap dilewati.');

            return self::SUCCESS;
        }

        $sent = 0;
        User::whereNotNull('telegram_chat_id')->each(function (User $user) use (&$sent) {
            $text = FamilyNotifier::weeklyRecapText($user, today());
            if ($text && Telegram::send($user->telegram_chat_id, $text)) {
                $sent++;
            }
        });

        $this->info("Rekap mingguan terkirim ke {$sent} orang tua.");

        return self::SUCCESS;
    }
}
