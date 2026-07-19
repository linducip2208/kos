<?php

return [
    'name'    => env('APP_NAME', 'Kos Manager'),
    'version' => '1.0.0',

    'invoice_prefix' => env('INVOICE_PREFIX', 'INV'),
    'lease_prefix'   => env('LEASE_PREFIX', 'KTR'),

    'reminder_days' => env('REMINDER_DAYS', 3),

    'max_properties' => env('MAX_PROPERTIES', null),
    'max_rooms'      => env('MAX_ROOMS', null),
];
