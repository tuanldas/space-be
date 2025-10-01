<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\EloquentRepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\UserService;
use App\Adapters\Interfaces\OAuthAdapterInterface;
use App\Adapters\OAuthAdapter;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\Interfaces\AbilityRepositoryInterface;
use App\Repositories\AbilityRepository;
use App\Services\Interfaces\RoleServiceInterface;
use App\Services\RoleService;
use App\Services\Interfaces\AbilityServiceInterface;
use App\Services\AbilityService;
use App\Repositories\Interfaces\TransactionCategoryRepositoryInterface;
use App\Repositories\TransactionCategoryRepository;
use App\Services\Interfaces\TransactionCategoryServiceInterface;
use App\Services\TransactionCategoryService;
use App\Adapters\FileAdapter;
use App\Adapters\Interfaces\FileAdapterInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\WalletRepository;
use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use App\Repositories\WalletTransactionRepository;
use App\Services\Interfaces\WalletServiceInterface;
use App\Services\WalletService;
use App\Services\Interfaces\WalletTransactionServiceInterface;
use App\Services\WalletTransactionService;
use App\Services\Interfaces\ChartServiceInterface;
use App\Services\ChartService;

class AppBindingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(AbilityRepositoryInterface::class, AbilityRepository::class);
        $this->app->bind(TransactionCategoryRepositoryInterface::class, TransactionCategoryRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(WalletTransactionRepositoryInterface::class, WalletTransactionRepository::class);
        
        // Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(AbilityServiceInterface::class, AbilityService::class);
        $this->app->bind(TransactionCategoryServiceInterface::class, TransactionCategoryService::class);
        $this->app->bind(WalletServiceInterface::class, WalletService::class);
        $this->app->bind(WalletTransactionServiceInterface::class, WalletTransactionService::class);
        $this->app->bind(ChartServiceInterface::class, ChartService::class);
        
        // Adapters
        $this->app->bind(OAuthAdapterInterface::class, OAuthAdapter::class);
        $this->app->bind(FileAdapterInterface::class, FileAdapter::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 