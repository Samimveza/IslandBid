<?php

$env = require __DIR__ . '/env.php';

return [
    'app' => [
        'env' => $env['APP_ENV'],
        'debug' => $env['APP_DEBUG'] === '1',
        'url' => $env['APP_URL'],
    ],
    'db' => [
        'host' => $env['DB_HOST'],
        'port' => $env['DB_PORT'],
        'name' => $env['DB_NAME'],
        'user' => $env['DB_USER'],
        'password' => $env['DB_PASSWORD'],
        'charset' => 'utf8',
    ],
    'session' => [
        'name' => $env['SESSION_NAME'],
        'lifetime' => (int) $env['SESSION_LIFETIME'],
    ],
];
