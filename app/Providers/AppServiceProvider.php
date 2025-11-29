<?php

namespace App\Providers;

use App\Utils\Functions\FunctionUtils;
use App\Utils\Models\CustomPersonalAccessToken;
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
            return FunctionUtils::getTokenFromRequest($tok);
        });
    }
}
