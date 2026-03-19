<?php

class SavedItemRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function isSaved(string $userId, string $itemId): bool
    {
        $stmt = $this->db->prepare('
            select 1 from saved_item
            where id_user = :id_user and id_item = :id_item
            limit 1
        ');
        $stmt->execute(['id_user' => $userId, 'id_item' => $itemId]);
        return (bool) $stmt->fetchColumn();
    }

    public function save(string $userId, string $itemId): void
    {
        if ($this->isSaved($userId, $itemId)) {
            return;
        }

        $stmt = $this->db->prepare('
            insert into saved_item (id_saved_item, id_user, id_item)
            values (:id_saved_item, :id_user, :id_item)
        ');
        $stmt->execute([
            'id_saved_item' => Util::uuid(),
            'id_user' => $userId,
            'id_item' => $itemId,
        ]);
    }

    public function unsave(string $userId, string $itemId): void
    {
        $stmt = $this->db->prepare('
            delete from saved_item
            where id_user = :id_user and id_item = :id_item
        ');
        $stmt->execute([
            'id_user' => $userId,
            'id_item' => $itemId,
        ]);
    }

    public function listIdsForUser(string $userId): array
    {
        $stmt = $this->db->prepare('
            select id_item
            from saved_item
            where id_user = :id_user
        ');
        $stmt->execute(['id_user' => $userId]);
        return array_column($stmt->fetchAll(), 'id_item');
    }
}

