<?php

namespace App\Providers;

use App\Modules\Workshop\Payments\Contracts\PaymentGateway;
use App\Modules\Workshop\Payments\Gateways\FakeQrisGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, FakeQrisGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
