<?php

namespace App\Providers;

use App\Adapters\Presenters\Image\GetImageJsonPresenter;
use App\Adapters\Presenters\LoginUser\LoginUserJsonPresenter;
use App\Adapters\Presenters\Wallet\GetWalletsJsonPresenter;
use App\Adapters\TokenGenerator\PassportTokenGenerator;
use App\Adapters\TokenGenerator\TokenGeneratorInterface;
use App\Domain\Factories\UserFactory;
use App\Domain\Repositories\ImageRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\WalletRepositoryInterface;
use App\Domain\UseCases\GetImage\GetImageInputPort;
use App\Domain\UseCases\GetImage\GetImageInteract;
use App\Domain\UseCases\LoginUser\LoginUserInputPort;
use App\Domain\UseCases\LoginUser\LoginUserInteract;
use App\Domain\UseCases\Wallets\GetWalletInputPort;
use App\Domain\UseCases\Wallets\GetWalletInteract;
use App\Factories\UserModelFactory;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\ImageController;
use App\Repositories\ImageRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;
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
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            WalletRepositoryInterface::class,
            WalletRepository::class
        );
        $this->app->bind(
            TokenGeneratorInterface::class,
            PassportTokenGenerator::class
        );
        $this->app->bind(
            ImageRepositoryInterface::class,
            ImageRepository::class
        );
        $this->app->when(AuthController::class)
            ->needs(LoginUserInputPort::class)
            ->give(function ($app) {
                return $app->make(LoginUserInteract::class, [
                    'output' => $app->make(LoginUserJsonPresenter::class),
                ]);
            });
        $this->app->when(WalletController::class)
            ->needs(GetWalletInputPort::class)
            ->give(function ($app) {
                return $app->make(GetWalletInteract::class, [
                    'output' => $app->make(GetWalletsJsonPresenter::class),
                ]);
            });
        $this->app->when(ImageController::class)
            ->needs(GetImageInputPort::class)
            ->give(function ($app) {
                return $app->make(GetImageInteract::class, [
                    'output' => $app->make(GetImageJsonPresenter::class),
                ]);
            });
    }

    public function boot(): void
    {
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::ignoreRoutes();
        ResourceCollection::withoutWrapping();
    }
}
