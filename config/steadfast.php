<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SteadFast Courier API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your SteadFast Courier API credentials and settings.
    | You can get these from your SteadFast Courier dashboard.
    |
    */

    'active' => env('STEADFAST_ACTIVE', false),
    'api_key' => env('STEADFAST_API_KEY', ''),
    'secret_key' => env('STEADFAST_SECRET_KEY', ''),
    'base_url' => env('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default Delivery Settings
    |--------------------------------------------------------------------------
    */
    'default_delivery_type' => env('STEADFAST_DEFAULT_DELIVERY_TYPE', 0), // 0 = home delivery, 1 = point delivery
    'max_bulk_orders' => 500, // Maximum orders in bulk request
];
