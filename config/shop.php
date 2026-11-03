<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store Currency & Formatting
    |--------------------------------------------------------------------------
    */
    'currency' => 'NPR',
    'currency_symbol' => 'Rs ',

    /*
    |--------------------------------------------------------------------------
    | Administrator Alert Email
    |--------------------------------------------------------------------------
    */
    'admin_email' => env('ADMIN_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Accessory Category Slugs
    |--------------------------------------------------------------------------
    |
    | Used to filter accessories on the storefront homepage and navigation.
    |
    */
    'accessory_category_slugs' => [
        'chargers-cables',
        'cases-covers',
        'audio-speakers',
        'power-banks',
        'accessories',
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Guarantees & Checking Periods
    |--------------------------------------------------------------------------
    |
    | check_guarantee_days — "7-day checking guarantee" shown site-wide.
    |
    */
    'guarantees' => [
        'check_guarantee_days' => env('SHOP_CHECK_GUARANTEE_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Lifecycle
    |--------------------------------------------------------------------------
    |
    | pending_expiry_hours — how long a COD order stays in "pending" before
    | staff should cancel it if the customer is uncontactable.
    |
    */
    'orders' => [
        'pending_expiry_hours' => env('SHOP_PENDING_EXPIRY_HOURS', 48),
    ],
];
