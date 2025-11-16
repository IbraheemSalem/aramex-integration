<?php

namespace Ibraheem\AramexIntegration;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Ibraheem\AramexIntegration\Console\SyncShipmentStatus;
use Ibraheem\AramexIntegration\Console\MonthlyBilling;

class AramexIntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/aramex.php',
            'aramex'
        );

        $this->app->singleton('aramex', function ($app) {
            return new Services\AramexService();
        });

        $this->app->singleton('aramex.billing', function ($app) {
            return new Services\BillingService();
        });

        $this->app->singleton('aramex.sms', function ($app) {
            return new Services\SMSService();
        });
    }

    /**
     * Register events and listeners.
     */
    protected function registerEventListeners(): void
    {
        $this->app['events']->listen(
            Events\ShipmentCreated::class,
            Listeners\SendShipmentSMS::class
        );

        $this->app['events']->listen(
            Events\ShipmentCreated::class,
            Listeners\RecordBillingTransaction::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/aramex.php' => config_path('aramex.php'),
        ], 'aramex-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'aramex-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/aramex'),
        ], 'aramex-views');

        // Publish public assets
        $this->publishes([
            __DIR__ . '/Resources/public' => public_path('vendor/aramex'),
        ], 'aramex-assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'aramex');

        // Register routes
        $this->registerRoutes();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncShipmentStatus::class,
                MonthlyBilling::class,
            ]);
        }

        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        // API Routes
        Route::group([
            'prefix' => config('aramex.route_prefix', 'api/aramex'),
            'middleware' => config('aramex.middleware', ['api']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        // Web Routes (Dashboard)
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }
}

