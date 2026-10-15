<?php

return [
    'base_url' => env('YALIDINE_BASE_URL', 'https://api.example.invalid'),

    'timeout' => (int) env('YALIDINE_TIMEOUT', 10),

    /*
     * These header names are configurable examples. Confirm the required
     * authentication scheme with the API provider before using this package.
     */
    'headers' => [
        env('YALIDINE_API_ID_HEADER', 'X-API-ID') =>
            env('YALIDINE_API_ID'),

        env('YALIDINE_API_TOKEN_HEADER', 'X-API-TOKEN') =>
            env('YALIDINE_API_TOKEN'),
    ],

    /*
     * Example paths only. Set these to the exact paths and placeholders
     * supported by the provider's current API.
     */
    'endpoints' => [
        'parcels' => '/parcels',
        'parcel' => '/parcels/{tracking}',
        'create_parcel' => '/parcels',
        'delivery_fees' => '/delivery-fees',
        'tracking' => '/tracking/{tracking}',
    ],
];
