<?php

$user = AuthMiddleware::requireAuth();
$db = Database::connection();
$repo = new SellerDashboardRepository($db);

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : null;
if ($status === 'all') {
    $status = null;
}

$listings = $repo->getListings($user['id_user'], $status);

$segmented = [
    'active' => array_values(array_filter($listings, static fn ($x) => $x['item_status'] === 'active')),
    'sold' => array_values(array_filter($listings, static fn ($x) => $x['item_status'] === 'sold')),
    'expired' => array_values(array_filter($listings, static fn ($x) => $x['item_status'] === 'expired')),
    'draft' => array_values(array_filter($listings, static fn ($x) => $x['item_status'] === 'draft')),
];

JsonResponse::success([
    'items' => $listings,
    'segmented' => $segmented,
]);

