<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Yabacon\Paystack;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Paystack::class, function ($app) {
            return new Paystack(
                config('app.env') === 'production'
                    ? env('PAYSTACK_SECRET_KEY')
                    : env('PAYSTACK_SECRET_KEY')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
