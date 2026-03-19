<?php

$user = AuthMiddleware::requireAuth();
$db = Database::connection();
$repo = new BidRepository($db);
$service = new BidService($repo);

$payload = Request::json();
$result = $service->place($user, $payload);

JsonResponse::success([
    'bid' => $result,
]);

