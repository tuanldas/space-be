<?php

use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\ChangeLanguage::class])->group(function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
        Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])
            ->middleware(['auth:api']);
    });
});
