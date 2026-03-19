<?php

class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function register(array $payload): array
    {
        $errors = Validator::required($payload, ['first_name', 'last_name', 'email', 'password']);
        if (!empty($errors)) {
            JsonResponse::error('Validation failed.', 422, $errors);
        }

        $email = strtolower(trim($payload['email']));
        if (!Validator::email($email)) {
            JsonResponse::error('Invalid email format.', 422);
        }

        if (!Validator::minLength((string) $payload['password'], 8)) {
            JsonResponse::error('Password must be at least 8 characters.', 422);
        }

        $existing = $this->users->findByEmail($email);
        if ($existing) {
            JsonResponse::error('Email is already in use.', 409);
        }

        $newUser = $this->users->create([
            'first_name' => trim((string) $payload['first_name']),
            'last_name' => trim((string) $payload['last_name']),
            'email' => $email,
            'phone' => trim((string) ($payload['phone'] ?? '')) ?: null,
            'password_hash' => password_hash((string) $payload['password'], PASSWORD_DEFAULT),
            'id_tenant' => 'TENANT_DEFAULT',
        ]);

        // Registration does NOT auto-login the user.
        return $newUser;
    }

    public function login(array $payload): array
    {
        if (empty($payload['email']) || empty($payload['password'])) {
            JsonResponse::error('Email and password are required.', 422);
        }

        $user = $this->users->findByEmail((string) $payload['email']);
        if (!$user || !password_verify((string) $payload['password'], (string) $user['password_hash'])) {
            JsonResponse::error('Invalid credentials.', 401);
        }

        if ((bool) $user['is_deactivated']) {
            JsonResponse::error('Account is deactivated.', 403);
        }

        $sessionUser = [
            'id_user' => $user['id_user'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
        ];
        SessionAuth::login($sessionUser);

        return $sessionUser;
    }
}
