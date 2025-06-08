<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\IntegrationManagerService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрация IntegrationManagerService
        $this->app->singleton(IntegrationManagerService::class, function ($app) {
            return new IntegrationManagerService();
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
