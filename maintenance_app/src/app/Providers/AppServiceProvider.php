<?php

namespace App\Providers;

use App\Notifications\Channels\TeamsWebhookChannel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Notification;
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
        Paginator::defaultView('vendor.pagination.tmq');
        Paginator::defaultSimpleView('vendor.pagination.tmq');

        Notification::extend('teams', fn () => new TeamsWebhookChannel);
    }
}
