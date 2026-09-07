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

];
