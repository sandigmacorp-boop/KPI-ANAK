<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Services\Telegram;
use App\Support\FamilyNotifier;
use Illuminate\Console\Command;

class SendSlotReminders extends Command
{
    protected $signature = 'sans:reminders {--slot= : Paksa slot tertentu (pagi/siang/sore/malam)}';

    protected $description = 'Kirim pengingat tugas slot berjalan ke Telegram orang tua';

    public function handle(): int
    {
        $slot = $this->option('slot') ?: Task::currentSlot();

        if (! $slot) {
            $this->info('Di luar jam slot mana pun — tidak ada pengingat.');

            return self::SUCCESS;
        }

        if (! Telegram::enabled()) {
            $this->warn('TELEGRAM_BOT_TOKEN belum diatur — pengingat dilewati.');

            return self::SUCCESS;
        }

        $sent = 0;
        User::whereNotNull('telegram_chat_id')->each(function (User $user) use ($slot, &$sent) {
            $text = FamilyNotifier::slotReminderText($user, $slot, today());
            if ($text && Telegram::send($user->telegram_chat_id, $text)) {
                $sent++;
            }
        });

        $this->info("Pengingat slot '{$slot}' terkirim ke {$sent} orang tua.");

        return self::SUCCESS;
    }
}
