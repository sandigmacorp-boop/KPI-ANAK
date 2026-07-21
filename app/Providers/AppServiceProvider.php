<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        $this->applyMailSettingsFromDatabase();
    }

    /**
     * Timpa konfigurasi mail dari pengaturan admin (disimpan lewat UI /admin/email), agar
     * tak perlu edit .env di server. Baca langsung (bukan firstOrCreate) & bungkus try/catch
     * supaya request pertama sebelum migrasi jalan (tabel belum ada) tetap jatuh ke .env.
     */
    private function applyMailSettingsFromDatabase(): void
    {
        try {
            $settings = AppSetting::query()->first();
        } catch (\Throwable) {
            return;
        }

        if (! $settings) {
            return;
        }

        if (filled($settings->mail_mailer)) {
            config(['mail.default' => $settings->mail_mailer]);
        }

        if (filled($settings->resend_api_key)) {
            config(['services.resend.key' => $settings->resend_api_key]);
        }

        if (filled($settings->mail_from_address)) {
            config([
                'mail.from.address' => $settings->mail_from_address,
                'mail.from.name' => $settings->mail_from_name ?: config('app.name'),
            ]);
        }
    }
}
