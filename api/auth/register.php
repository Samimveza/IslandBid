<?php

$db = Database::connection();
$users = new UserRepository($db);
$auth = new AuthService($users);

$payload = Request::json();
$user = $auth->register($payload);

JsonResponse::success([
    'user' => $user,
    'message' => 'Registration successful. You can now log in.',
], 201);
