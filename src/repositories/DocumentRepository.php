<?php

class DocumentRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function createImageDocument(array $meta, array $docType, array $physicalParam, array $serverParam): array
    {
        $idDocument = Util::uuid();

        $stmt = $this->db->prepare('
            insert into document (
                id_document,
                file_name,
                file_extension,
                document_order,
                is_deactivated,
                id_document_type,
                physical_file_path,
                id_parameter_base_physical_file_path,
                server_file_path,
                id_parameter_base_server_url,
                id_tenant
            ) values (
                :id_document,
                :file_name,
                :file_extension,
                :document_order,
                false,
                :id_document_type,
                :physical_file_path,
                :id_parameter_base_physical_file_path,
                :server_file_path,
                :id_parameter_base_server_url,
                :id_tenant
            )
        ');

        $stmt->execute([
            'id_document' => $idDocument,
            'file_name' => $meta['original_name'],
            'file_extension' => $meta['file_extension'],
            'document_order' => $meta['document_order'] ?? null,
            'id_document_type' => $docType['id_document_type'],
            'physical_file_path' => $meta['physical_path'],
            'id_parameter_base_physical_file_path' => $physicalParam['id_parameter'],
            'server_file_path' => $meta['server_path'],
            'id_parameter_base_server_url' => $serverParam['id_parameter'],
            'id_tenant' => 'TENANT_DEFAULT',
        ]);

        return [
            'id_document' => $idDocument,
            'server_file_path' => $meta['server_path'],
            'file_name' => $meta['original_name'],
            'file_extension' => $meta['file_extension'],
        ];
    }

    public function linkToItem(string $idItem, string $idDocument, ?int $displayOrder, bool $isPrimary): array
    {
        $idItemDocument = Util::uuid();

        $stmt = $this->db->prepare('
            insert into item_document (
                id_item_document,
                id_item,
                id_document,
                display_order,
                is_primary
            ) values (
                :id_item_document,
                :id_item,
                :id_document,
                :display_order,
                :is_primary
            )
        ');

        $stmt->execute([
            'id_item_document' => $idItemDocument,
            'id_item' => $idItem,
            'id_document' => $idDocument,
            'display_order' => $displayOrder,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);

        return [
            'id_item_document' => $idItemDocument,
        ];
    }
}

