<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aramex API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'sandbox_url' => env('ARAMEX_SANDBOX_URL', 'https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json'),
        'production_url' => env('ARAMEX_PRODUCTION_URL', 'https://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json'),
        'timeout' => env('ARAMEX_API_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route_prefix' => env('ARAMEX_ROUTE_PREFIX', 'api/aramex'),
    'middleware' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Billing Configuration
    |--------------------------------------------------------------------------
    */
    'billing' => [
        'monthly_subscription_fee' => env('ARAMEX_MONTHLY_FEE', 100.00),
        'per_shipment_fee' => env('ARAMEX_PER_SHIPMENT_FEE', 5.00),
        'free_shipment_quota' => env('ARAMEX_FREE_QUOTA', 10),
        'currency' => env('ARAMEX_CURRENCY', 'SAR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'enabled' => env('ARAMEX_SMS_ENABLED', true),
        'provider' => env('ARAMEX_SMS_PROVIDER', 'twilio'),
        'from' => env('ARAMEX_SMS_FROM', 'Aramex'),
        'template' => env('ARAMEX_SMS_TEMPLATE', 'Your shipment {tracking_number} has been created. Track: {tracking_url}'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'secret' => env('ARAMEX_WEBHOOK_SECRET'),
        'log_all' => env('ARAMEX_WEBHOOK_LOG_ALL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('ARAMEX_QUEUE_CONNECTION', 'default'),
        'queue' => env('ARAMEX_QUEUE_NAME', 'aramex'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Label Configuration
    |--------------------------------------------------------------------------
    */
    'label' => [
        'save_path' => env('ARAMEX_LABEL_PATH', storage_path('app/aramex/labels')),
        'disk' => env('ARAMEX_LABEL_DISK', 'local'),
    ],
];

