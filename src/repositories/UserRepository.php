<?php

class UserRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('
            select id_user, first_name, last_name, email, password_hash, is_deactivated
            from app_user
            where lower(email) = lower(:email)
            limit 1
        ');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(array $payload): array
    {
        $id = Util::uuid();
        $stmt = $this->db->prepare('
            insert into app_user (
                id_user,
                first_name,
                last_name,
                email,
                phone,
                password_hash,
                is_email_verified,
                is_phone_verified,
                is_deactivated,
                id_tenant,
                date_created_utc
            ) values (
                :id_user,
                :first_name,
                :last_name,
                :email,
                :phone,
                :password_hash,
                :is_email_verified,
                :is_phone_verified,
                :is_deactivated,
                :id_tenant,
                now()
            )
        ');
        $stmt->execute([
            'id_user' => $id,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => strtolower(trim($payload['email'])),
            'phone' => $payload['phone'] ?? null,
            'password_hash' => $payload['password_hash'],
            'is_email_verified' => 1,
            'is_phone_verified' => 1,
            'is_deactivated' => 0,
            'id_tenant' => $payload['id_tenant'] ?? 'TENANT_DEFAULT',
        ]);

        return [
            'id_user' => $id,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => strtolower(trim($payload['email'])),
        ];
    }
}
