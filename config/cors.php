<?php

return [

    'paths' => ['api/*','login',
    'logout','broadcasting/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

   'allowed_origins' => [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'https://alumni-frontend-khaki.vercel.app'
],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];