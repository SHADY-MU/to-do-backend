<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
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
        VerifyEmail::createUrlUsing(function ($notifiable) {
            // Point to the API route so it verifies immediately when clicked
            return "http://127.0.0.1:8000/api/email/verify/{$notifiable->getKey()}/" . sha1($notifiable->getEmailForVerification());
        });
    }
}
