<?php

namespace App\Providers;

use App\Models\CustomPersonalAccessToken;
use App\Services\Utils;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(CustomPersonalAccessToken::class);
        Sanctum::getAccessTokenFromRequestUsing(function ($tok) {
            return Utils::getTokenFromRequest($tok);
        });
    }
}
