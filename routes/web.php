<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.coming-soon');
})->name('coming-soon');
