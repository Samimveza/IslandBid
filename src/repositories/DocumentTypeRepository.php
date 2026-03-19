<?php

class DocumentTypeRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getByCode(string $code): ?array
    {
        $stmt = $this->db->prepare('
            select id_document_type, document_type_name, document_type_code
            from document_type
            where document_type_code = :code
              and is_deactivated = false
            limit 1
        ');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

