<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email : Email akun yang akan dijadikan admin}
                            {--revoke : Cabut status admin, bukan berikan}';

    protected $description = 'Jadikan (atau cabut) akun sebagai admin dashboard platform';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("Tidak ada akun dengan email: {$this->argument('email')}");

            return self::FAILURE;
        }

        $revoke = (bool) $this->option('revoke');
        $user->forceFill(['is_admin' => ! $revoke])->save();

        $this->info($revoke
            ? "{$user->email} tidak lagi admin."
            : "{$user->email} sekarang admin — bisa membuka /admin.");

        return self::SUCCESS;
    }
}
