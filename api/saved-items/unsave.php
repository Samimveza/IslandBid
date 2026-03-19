<?php

$user = AuthMiddleware::requireAuth();
$db = Database::connection();
$repo = new SavedItemRepository($db);

$payload = Request::json();
$idItem = trim((string) ($payload['id_item'] ?? ''));

if ($idItem === '') {
    JsonResponse::error('id_item is required.', 422);
}

$repo->unsave($user['id_user'], $idItem);

JsonResponse::success([
    'saved' => false,
]);

