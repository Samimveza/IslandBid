<?php

class AuthMiddleware
{
    public static function requireAuth(): array
    {
        $user = SessionAuth::user();
        if (!$user) {
            JsonResponse::error('Authentication required.', 401);
        }

        return $user;
    }
}
