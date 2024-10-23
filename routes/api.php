<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
});
