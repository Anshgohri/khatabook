<?php

return [

    /*
    |--------------------------------------------------------------------------
    | High-Value Transaction Threshold
    |--------------------------------------------------------------------------
    |
    | Sales or expenses at or above this amount trigger a notification to
    | Admin and Manager users.
    |
    */

    'high_value_threshold' => (float) env('KHATABOOK_HIGH_VALUE_THRESHOLD', 10000),

    /*
    |--------------------------------------------------------------------------
    | Global Store Profile & Invoice Credentials
    |--------------------------------------------------------------------------
    |
    | Centralized store credentials used across invoices, headers, and reports.
    |
    */

    'store_name' => env('STORE_NAME', 'Shashi Bala Baans Store'),
    'store_proprietor' => env('STORE_PROPRIETOR', 'Ashok Kumar'),
    'store_subtitle' => env('STORE_SUBTITLE', 'Direct Timber Merchant • Raw Bamboo, Ghodi, Chaali & Siddhi'),
    'store_address' => env('STORE_ADDRESS', 'House No 2755, Opposite Gaushala Road, Janak Puri, Karnal, Haryana - 132001'),
    'store_phone' => env('STORE_PHONE', '9255523276 | 8950304888'),
    'store_email' => env('STORE_EMAIL', 'anshgohri8950@gmail.com'),
    'store_terms' => env('STORE_TERMS', "1. Goods once sold are strictly governed under timber yard standard policies.\n2. Raw bamboo poles & Ghodi trestles are checked before dispatch.\n3. Thank you for doing business with Shashi Bala Baans Store!"),
    'invoice_prefix' => env('INVOICE_PREFIX', 'INV-'),

];
