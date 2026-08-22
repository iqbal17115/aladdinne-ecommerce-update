<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_AI_PKEY'),
    ],

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),

        // Conversions API (server-side). Leave the token empty to keep CAPI off
        // and rely on the browser pixel alone.
        'capi_access_token' => env('META_CAPI_ACCESS_TOKEN'),
        'test_event_code' => env('META_CAPI_TEST_EVENT_CODE'),
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),

        // Load the pixel even on the local environment. Handy while testing with
        // Meta's "Test Events" tool; keep it false in day-to-day development.
        'pixel_on_local' => env('META_PIXEL_ON_LOCAL', false),
    ],

];
