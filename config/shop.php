<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store Currency & Formatting
    |--------------------------------------------------------------------------
    */
    'currency'        => 'NPR',
    'currency_symbol' => 'Rs ',

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
    | Delivery Configuration
    |--------------------------------------------------------------------------
    |
    | FREE_DELIVERY_THRESHOLD — order total (NPR) above which delivery is free.
    | Set to 0 to always charge, or a very high number for always-free.
    |
    | INSIDE_VALLEY_FEE  — flat fee (NPR) for Kathmandu Valley deliveries.
    | OUTSIDE_VALLEY_FEE — flat fee (NPR) for outside-valley / nationwide.
    |
    */
    'delivery' => [
        'free_threshold'     => env('SHOP_FREE_DELIVERY_THRESHOLD', 0),
        'inside_valley_fee'  => env('SHOP_INSIDE_VALLEY_FEE', 0),
        'outside_valley_fee' => env('SHOP_OUTSIDE_VALLEY_FEE', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Guarantees & Warranty Periods
    |--------------------------------------------------------------------------
    |
    | check_guarantee_days — "7-day checking guarantee" shown site-wide.
    | warranty_new_days    — implied store warranty period for brand-new units.
    |
    | These values drive copy in Blade views. Update here, see it everywhere.
    |
    */
    'guarantees' => [
        'check_guarantee_days' => env('SHOP_CHECK_GUARANTEE_DAYS', 7),
        'warranty_new_days'    => env('SHOP_WARRANTY_NEW_DAYS', 365),
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
