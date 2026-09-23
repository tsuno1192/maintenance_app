<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * アプリケーション共通サービスプロバイダ。
 */
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
     *
     * ページネーションを Tailwind ビューに統一し、
     * 現場向け UI の見た目を揃える。
     */
    public function boot(): void
    {
        error_reporting(E_ALL & ~E_NOTICE);
    }
}
