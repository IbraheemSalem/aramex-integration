<?php

use Illuminate\Support\Facades\Route;

// Dashboard route
Route::get('/aramex/dashboard', function () {
    return view('aramex::dashboard.index', [
        'merchantId' => request()->get('merchant_id', '1'),
        'apiKey' => request()->get('api_key', ''),
    ]);
})->name('aramex.dashboard');

