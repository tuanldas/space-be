<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TransactionCategoryController;

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
    
    Route::apiResource('users', UserController::class);
    
    Route::apiResource('roles', RoleController::class);
    Route::get('/abilities', [RoleController::class, 'abilities']);
    Route::post('/users/assign-role', [RoleController::class, 'assignRoleToUser']);
    Route::post('/users/remove-role', [RoleController::class, 'removeRoleFromUser']);
    Route::get('/roles/{roleName}/users', [RoleController::class, 'getUsersByRole']);
    
    // Transaction Categories Routes
    Route::get('transaction-categories/trashed', [TransactionCategoryController::class, 'trashed']);
    Route::post('transaction-categories/{transaction_category}/restore', [TransactionCategoryController::class, 'restore']);
    Route::delete('transaction-categories/{transaction_category}/force', [TransactionCategoryController::class, 'forceDelete']);
    Route::apiResource('transaction-categories', TransactionCategoryController::class);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!'
    ]);
}); 