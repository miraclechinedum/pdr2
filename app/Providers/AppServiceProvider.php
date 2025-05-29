<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Yabacon\Paystack;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Paystack::class, function ($app) {
            $client = new Client([
                'timeout' => 10,
            ]);

            return new Paystack(
                config('services.paystack.secret'),
                null,
                $client
            );
        });
    }

    public function boot()
    {
        // No need to do anything here for Paystack.
    }
}
