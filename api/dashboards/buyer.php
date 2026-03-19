<?php

$user = AuthMiddleware::requireAuth();
$db = Database::connection();
$repo = new BuyerDashboardRepository($db);

$activeBids = $repo->getActiveBids($user['id_user']);
$wonItems = $repo->getWonItems($user['id_user']);
$lostItems = $repo->getLostItems($user['id_user']);
$savedItems = $repo->getSavedItems($user['id_user']);

JsonResponse::success([
    'active_bids' => $activeBids,
    'won_items' => $wonItems,
    'lost_items' => $lostItems,
    'saved_items' => $savedItems,
]);

