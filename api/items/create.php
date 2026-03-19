<?php

$user = AuthMiddleware::requireAuth();

$db = Database::connection();
$itemRepo = new ItemRepository($db);
$categoryRepo = new CategoryRepository($db);
$itemService = new ItemService($itemRepo);

$payload = Request::json();
$idCategory = (string) ($payload['id_category'] ?? '');

if ($idCategory === '') {
    JsonResponse::error('id_category is required.', 422);
}

$categoryFields = $categoryRepo->getActiveFieldsWithOptions($idCategory);

$result = $itemService->createItem($user, $payload, $categoryFields);

JsonResponse::success([
    'item' => $result,
]);

