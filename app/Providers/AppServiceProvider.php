<?php

namespace App\Providers;

use App\Domain\Factories\UserFactory;
use App\Factories\UserModelFactory;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserFactory::class,
            UserModelFactory::class
        );
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::ignoreRoutes();
    }
}
