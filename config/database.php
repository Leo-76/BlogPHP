<?php
return [
    'default' => env('DB_CONNECTION', 'sqlite'),
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => env('DB_DATABASE', 'database/database.sqlite'),
        ],
        'mysql'  => [
            'driver'   => 'mysql',
            'host'     => env('DB_HOST', '127.0.0.1'),
            'port'     => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'blog'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4',
        ],
    ],
];
