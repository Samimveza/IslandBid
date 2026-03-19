<?php

$categoryId = (string) ($_GET['id_category'] ?? '');
$categoryId = trim($categoryId);

if ($categoryId === '') {
    JsonResponse::error('id_category is required.', 422);
}

$db = Database::connection();
$repo = new CategoryRepository($db);
$service = new CategoryService($repo);

$fields = $service->getFields($categoryId);

JsonResponse::success([
    'fields' => $fields,
]);

