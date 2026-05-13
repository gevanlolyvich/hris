<?php
return [
    'vapid' => [
        'public_key'  => env('PUSH_PUBLIC_KEY'),
        'private_key' => env('PUSH_PRIVATE_KEY'),
        'subject'     => env('APP_URL'),
    ],
];
