<?php

$user = SessionAuth::user();
if (!$user) {
    JsonResponse::error('Not authenticated.', 401);
}

JsonResponse::success(['user' => $user]);
