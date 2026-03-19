<?php

$db = Database::connection();
$repo = new ItemRepository($db);
$service = new ItemService($repo);

$slug = (string) ($_GET['slug'] ?? '');
$details = $service->getItemDetailsBySlug($slug);

if (!$details) {
    JsonResponse::error('Item not found.', 404);
}

JsonResponse::success($details);

