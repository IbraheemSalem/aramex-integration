<?php

use Illuminate\Support\Facades\Route;
use Ibraheem\AramexIntegration\Http\Controllers\MerchantAccountController;
use Ibraheem\AramexIntegration\Http\Controllers\AramexShipmentController;
use Ibraheem\AramexIntegration\Http\Controllers\RateController;
use Ibraheem\AramexIntegration\Http\Controllers\BillingController;
use Ibraheem\AramexIntegration\Http\Controllers\WebhookController;
use Ibraheem\AramexIntegration\Http\Middleware\ApiKeyMiddleware;

// Public webhook route (no auth required)
Route::post('/webhook/{merchantId?}', [WebhookController::class, 'handle']);

// Protected routes (require API key)
Route::middleware([ApiKeyMiddleware::class])->group(function () {
    // Account Management
    Route::prefix('account')->group(function () {
        Route::post('/connect', [MerchantAccountController::class, 'connect']);
        Route::get('/', [MerchantAccountController::class, 'getAccount']);
        Route::put('/', [MerchantAccountController::class, 'updateAccount']);
        Route::delete('/', [MerchantAccountController::class, 'deleteAccount']);
    });

    // Shipment Management
    Route::prefix('shipments')->group(function () {
        Route::post('/', [AramexShipmentController::class, 'createShipment']);
        Route::get('/', [AramexShipmentController::class, 'getShipments']);
        Route::get('/track/{trackingNumber}', [AramexShipmentController::class, 'trackShipment']);
    });

    // Rate Calculator
    Route::post('/rate/calculate', [RateController::class, 'calculateRate']);

    // Billing
    Route::prefix('billing')->group(function () {
        Route::get('/history', [BillingController::class, 'getBillingHistory']);
        Route::get('/transactions', [BillingController::class, 'getTransactions']);
        Route::get('/free-quota', [BillingController::class, 'getFreeQuota']);
    });
});

