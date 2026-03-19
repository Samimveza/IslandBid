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

    if ($path === '/api/items/edit-data' && $method === 'GET') {
        require __DIR__ . '/../api/items/edit-data.php';
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

    if ($path === '/api/bids/place' && $method === 'POST') {
        require __DIR__ . '/../api/bids/place.php';
        exit;
    }

    if ($path === '/api/bids/by-item' && $method === 'GET') {
        require __DIR__ . '/../api/bids/by-item.php';
        exit;
    }

    if ($path === '/api/bids/update' && $method === 'POST') {
        require __DIR__ . '/../api/bids/update.php';
        exit;
    }

    if ($path === '/api/bids/remove' && $method === 'POST') {
        require __DIR__ . '/../api/bids/remove.php';
        exit;
    }

    if ($path === '/api/saved-items/save' && $method === 'POST') {
        require __DIR__ . '/../api/saved-items/save.php';
        exit;
    }

    if ($path === '/api/saved-items/unsave' && $method === 'POST') {
        require __DIR__ . '/../api/saved-items/unsave.php';
        exit;
    }

    if ($path === '/api/saved-items' && $method === 'GET') {
        require __DIR__ . '/../api/saved-items/list.php';
        exit;
    }

    if ($path === '/api/dashboards/seller' && $method === 'GET') {
        require __DIR__ . '/../api/dashboards/seller.php';
        exit;
    }

    if ($path === '/api/dashboards/buyer' && $method === 'GET') {
        require __DIR__ . '/../api/dashboards/buyer.php';
        exit;
    }

    JsonResponse::error('Route not found.', 404);
} catch (PDOException $e) {
    JsonResponse::error('Database error.', 500, ['detail' => $e->getMessage()]);
} catch (Throwable $e) {
    JsonResponse::error('Server error.', 500, ['detail' => $e->getMessage()]);
}
