<?php

require_once __DIR__ . '/EnvironmentLoader.php';
EnvironmentLoader::load(dirname(__DIR__, 2) . '/.env');

return [
    'APP_ENV' => getenv('APP_ENV') ?: 'development',
    'APP_DEBUG' => getenv('APP_DEBUG') ?: '1',
    'APP_URL' => getenv('APP_URL') ?: 'http://islandbid.mu',
    'DB_HOST' => getenv('DB_HOST') ?: '127.0.0.1',
    'DB_PORT' => getenv('DB_PORT') ?: '5432',
    'DB_NAME' => getenv('DB_NAME') ?: 'islandbid',
    'DB_USER' => getenv('DB_USER') ?: 'postgres',
    'DB_PASSWORD' => getenv('DB_PASSWORD') ?: 'postgres',
    'SESSION_NAME' => getenv('SESSION_NAME') ?: 'islandbid_session',
    'SESSION_LIFETIME' => getenv('SESSION_LIFETIME') ?: '7200',
    'UPLOAD_PHYSICAL_BASE' => getenv('UPLOAD_PHYSICAL_BASE') ?: '/var/www/uploads',
    'UPLOAD_SERVER_FOLDER' => getenv('UPLOAD_SERVER_FOLDER') ?: '/uploads',
];
