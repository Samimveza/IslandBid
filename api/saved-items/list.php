<?php

$user = AuthMiddleware::requireAuth();
$db = Database::connection();
$repo = new SavedItemRepository($db);

$ids = $repo->listIdsForUser($user['id_user']);

JsonResponse::success([
    'item_ids' => $ids,
]);

