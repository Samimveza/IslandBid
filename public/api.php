<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap/api_bootstrap.php';

$path = Request::path();
$method = Request::method();

try {
    if ($path === '/api/auth/register' && $method === 'POST') {
        require __DIR__ . '/../api/auth/register.php';
        exit;
    }

    if ($path === '/api/auth/login' && $method === 'POST') {
        require __DIR__ . '/../api/auth/login.php';
        exit;
    }

    if ($path === '/api/auth/logout' && $method === 'POST') {
        require __DIR__ . '/../api/auth/logout.php';
        exit;
    }

    if ($path === '/api/auth/me' && $method === 'GET') {
        require __DIR__ . '/../api/auth/me.php';
        exit;
    }

    if ($path === '/api/items' && $method === 'GET') {
        require __DIR__ . '/../api/items/list.php';
        exit;
    }

    if ($path === '/api/items/detail' && $method === 'GET') {
        require __DIR__ . '/../api/items/detail.php';
        exit;
    }

    if ($path === '/api/categories' && $method === 'GET') {
        require __DIR__ . '/../api/categories/list.php';
        exit;
    }

    if ($path === '/api/categories/fields' && $method === 'GET') {
        require __DIR__ . '/../api/categories/fields.php';
        exit;
    }

    if ($path === '/api/items' && $method === 'POST') {
        require __DIR__ . '/../api/items/create.php';
        exit;
    }

    if ($path === '/api/items/upload-image' && $method === 'POST') {
        require __DIR__ . '/../api/items/upload-image.php';
        exit;
    }

    JsonResponse::error('Route not found.', 404);
} catch (PDOException $e) {
    JsonResponse::error('Database error.', 500, ['detail' => $e->getMessage()]);
} catch (Throwable $e) {
    JsonResponse::error('Server error.', 500, ['detail' => $e->getMessage()]);
}
