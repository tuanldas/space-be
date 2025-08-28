<?php

use App\Enums\AbilityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TransactionCategoryController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WalletTransactionController;
use App\Http\Controllers\Api\UserTransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the Application directly through bootstrap/app.php
| configuration and assigned the "api" middleware group.
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User Management Routes
    Route::middleware('can:' . AbilityType::VIEW_USERS->value)->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });
    
    Route::post('/users', [UserController::class, 'store'])->middleware('can:' . AbilityType::CREATE_USERS->value);
    Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])->middleware('can:' . AbilityType::UPDATE_USERS->value);
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:' . AbilityType::DELETE_USERS->value);
    
    // Role Management Routes
    Route::middleware('can:' . AbilityType::MANAGE_ROLES->value)->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::get('/abilities', [RoleController::class, 'abilities']);
    Route::post('/users/assign-role', [RoleController::class, 'assignRoleToUser']);
    Route::post('/users/remove-role', [RoleController::class, 'removeRoleFromUser']);
    Route::get('/roles/{roleName}/users', [RoleController::class, 'getUsersByRole']);
    });
    
    // Transaction Categories Routes
    Route::middleware('can:' . AbilityType::VIEW_TRANSACTION_CATEGORIES->value)->group(function () {
        Route::get('/transaction-categories', [TransactionCategoryController::class, 'index']);
        Route::get('/transaction-categories/trashed', [TransactionCategoryController::class, 'trashed']);
        Route::get('/transaction-categories/{transaction_category}', [TransactionCategoryController::class, 'show']);
        // Lightweight options
        Route::get('/transaction-categories-options', [TransactionCategoryController::class, 'options']);
    });
    
    Route::post('/transaction-categories', [TransactionCategoryController::class, 'store'])
        ->middleware('can:' . AbilityType::CREATE_TRANSACTION_CATEGORIES->value);
    
    Route::match(['put', 'patch'], '/transaction-categories/{transaction_category}', [TransactionCategoryController::class, 'update'])
        ->middleware('can:' . AbilityType::UPDATE_TRANSACTION_CATEGORIES->value);
    
    Route::delete('/transaction-categories/{transaction_category}', [TransactionCategoryController::class, 'destroy'])
        ->middleware('can:' . AbilityType::DELETE_TRANSACTION_CATEGORIES->value);
    
    Route::post('/transaction-categories/{transaction_category}/restore', [TransactionCategoryController::class, 'restore'])
        ->middleware('can:' . AbilityType::RESTORE_TRANSACTION_CATEGORIES->value);
    
    Route::delete('/transaction-categories/{transaction_category}/force', [TransactionCategoryController::class, 'forceDelete'])
        ->middleware('can:' . AbilityType::FORCE_DELETE_TRANSACTION_CATEGORIES->value);
    
    // Wallets Routes
    Route::apiResource('wallets', WalletController::class);
    Route::get('/wallets-sidebar', [WalletController::class, 'getSummaryForSidebar']);
    // Lightweight wallet options
    Route::get('/wallets-options', [WalletController::class, 'options']);
    
    // Nested Wallet Transactions Routes (RESTful)
    Route::apiResource('wallets.transactions', WalletTransactionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // User Transactions Routes
    Route::get('/user/transactions', [UserTransactionController::class, 'index']);
});

