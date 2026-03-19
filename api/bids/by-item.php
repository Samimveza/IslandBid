<?php

$db = Database::connection();
$itemRepo = new ItemRepository($db);

$idItem = (string) ($_GET['id_item'] ?? '');
$idItem = trim($idItem);

if ($idItem === '') {
    JsonResponse::error('id_item is required.', 422);
}

$bids = $itemRepo->getBids($idItem);

JsonResponse::success([
    'bids' => $bids,
]);

