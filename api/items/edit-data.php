<?php

$user = AuthMiddleware::requireAuth();
$db = Database::connection();
$repo = new ItemRepository($db);

$idItem = trim((string) ($_GET['id_item'] ?? ''));
if ($idItem === '') {
    JsonResponse::error('id_item is required.', 422);
}

$item = $repo->getOwnedItemForEdit($idItem, $user['id_user']);
if (!$item) {
    JsonResponse::error('Item not found.', 404);
}

$fields = $repo->getCategoryFieldsWithValues($idItem, $item['id_category']);
$images = $repo->getImages($idItem);

JsonResponse::success([
    'item' => $item,
    'fields' => $fields,
    'images' => $images,
]);

