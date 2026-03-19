<?php

$db = Database::connection();
$users = new UserRepository($db);
$auth = new AuthService($users);

$payload = Request::json();
$user = $auth->login($payload);

JsonResponse::success([
    'user' => $user,
]);
