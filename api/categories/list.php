<?php

$db = Database::connection();
$repo = new CategoryRepository($db);
$service = new CategoryService($repo);

$categories = $service->listActive();

JsonResponse::success([
    'categories' => $categories,
]);

