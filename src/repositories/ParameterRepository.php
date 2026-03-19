<?php

class ParameterRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getByCode(string $code): ?array
    {
        $stmt = $this->db->prepare('
            select id_parameter, paramater_value, code, id_tenant
            from parameter
            where code = :code
              and is_deactivated = false
            limit 1
        ');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

