<?php

class ItemRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        $sql = "
            select
                i.*,
                c.category_name
            from item i
            join category c on c.id_category = i.id_category
            where i.seo_slug = :slug
              and i.is_active = true
              and i.is_published = true
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    public function incrementViewCount(string $idItem): void
    {
        $stmt = $this->db->prepare('
            update item
            set view_count = view_count + 1
            where id_item = :id_item
        ');
        $stmt->execute(['id_item' => $idItem]);
    }

    public function getImages(string $idItem): array
    {
        $sql = "
            select
                (rtrim(p.paramater_value, '/') || '/' || ltrim(d.server_file_path, '/')) as server_file_path,
                d.physical_file_path,
                d.file_name,
                d.file_extension,
                idoc.is_primary,
                idoc.display_order
            from item_document idoc
            join document d on d.id_document = idoc.id_document
            join parameter p on p.id_parameter = d.id_parameter_base_server_url
            where idoc.id_item = :id_item
              and d.is_deactivated = false
            order by idoc.is_primary desc, idoc.display_order nulls last, d.date_created_utc desc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_item' => $idItem]);
        return $stmt->fetchAll();
    }

    public function getCategoryFieldsWithValues(string $idItem, string $idCategory): array
    {
        $sql = "
            select
                cf.id_category_field,
                cf.field_name,
                cf.field_label,
                cf.field_type,
                cf.display_order,
                v.field_value_text,
                v.field_value_number,
                v.field_value_boolean,
                v.field_value_date,
                v.field_value_option
            from category_field cf
            left join item_field_value v
                on v.id_category_field = cf.id_category_field
               and v.id_item = :id_item
            where cf.id_category = :id_category
              and cf.is_active = true
            order by cf.display_order nulls last, cf.date_created_utc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_item' => $idItem,
            'id_category' => $idCategory,
        ]);
        $fields = $stmt->fetchAll();

        if (!$fields) {
            return [];
        }

        // For select fields, resolve option labels
        $selectFieldIds = array_column(
            array_filter($fields, static fn ($f) => $f['field_type'] === 'select'),
            'id_category_field'
        );

        $optionsByField = [];
        if ($selectFieldIds) {
            $in = implode(',', array_fill(0, count($selectFieldIds), '?'));
            $optSql = "
                select
                    id_category_field,
                    option_value,
                    option_label
                from category_field_option
                where id_category_field in ({$in})
                  and is_active = true
            ";
            $optStmt = $this->db->prepare($optSql);
            $optStmt->execute($selectFieldIds);
            foreach ($optStmt->fetchAll() as $row) {
                $optionsByField[$row['id_category_field']][$row['option_value']] = $row['option_label'];
            }
        }

        foreach ($fields as &$field) {
            if ($field['field_type'] === 'select' && $field['field_value_option'] !== null) {
                $idField = $field['id_category_field'];
                $value = $field['field_value_option'];
                $field['field_value_label'] = $optionsByField[$idField][$value] ?? $value;
            }
        }
        unset($field);

        return $fields;
    }

    public function getBids(string $idItem): array
    {
        $sql = "
            select
                b.id_bid,
                b.id_user,
                b.bid_amount,
                b.bid_status,
                b.bid_time_utc,
                u.first_name,
                u.last_name
            from bid b
            join app_user u on u.id_user = b.id_user
            where b.id_item = :id_item
              and b.bid_status in ('active', 'updated', 'won', 'lost')
            order by b.bid_time_utc desc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_item' => $idItem]);
        return $stmt->fetchAll();
    }

    public function findOwnedItem(string $idItem, string $idUser): ?array
    {
        $stmt = $this->db->prepare('
            select id_item, id_user, seo_slug
            from item
            where id_item = :id_item
              and id_user = :id_user
        ');
        $stmt->execute([
            'id_item' => $idItem,
            'id_user' => $idUser,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getOwnedItemForEdit(string $idItem, string $idUser): ?array
    {
        $stmt = $this->db->prepare('
            select
                i.*
            from item i
            where i.id_item = :id_item
              and i.id_user = :id_user
            limit 1
        ');
        $stmt->execute([
            'id_item' => $idItem,
            'id_user' => $idUser,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $seoSlug): bool
    {
        $stmt = $this->db->prepare('
            select 1
            from item
            where seo_slug = :seo_slug
            limit 1
        ');
        $stmt->execute(['seo_slug' => $seoSlug]);
        return (bool) $stmt->fetchColumn();
    }

    public function createItemWithFields(array $itemData, array $fieldValues): array
    {
        $this->db->beginTransaction();
        try {
            $idItem = Util::uuid();
            $sql = '
                insert into item (
                    id_item,
                    id_user,
                    id_category,
                    title,
                    short_description,
                    description,
                    listing_type,
                    item_status,
                    start_price,
                    fixed_price,
                    bid_start_utc,
                    bid_end_utc,
                    currency_code,
                    location_text,
                    seo_slug,
                    meta_title,
                    meta_description,
                    is_published,
                    is_active
                ) values (
                    :id_item,
                    :id_user,
                    :id_category,
                    :title,
                    :short_description,
                    :description,
                    :listing_type,
                    :item_status,
                    :start_price,
                    :fixed_price,
                    :bid_start_utc,
                    :bid_end_utc,
                    :currency_code,
                    :location_text,
                    :seo_slug,
                    :meta_title,
                    :meta_description,
                    :is_published,
                    :is_active
                )
            ';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_item' => $idItem,
                'id_user' => $itemData['id_user'],
                'id_category' => $itemData['id_category'],
                'title' => $itemData['title'],
                'short_description' => $itemData['short_description'],
                'description' => $itemData['description'],
                'listing_type' => $itemData['listing_type'],
                'item_status' => $itemData['item_status'],
                'start_price' => $itemData['start_price'],
                'fixed_price' => $itemData['fixed_price'],
                'bid_start_utc' => $itemData['bid_start_utc'],
                'bid_end_utc' => $itemData['bid_end_utc'],
                'currency_code' => $itemData['currency_code'],
                'location_text' => $itemData['location_text'],
                'seo_slug' => $itemData['seo_slug'],
                'meta_title' => $itemData['meta_title'],
                'meta_description' => $itemData['meta_description'],
                'is_published' => $itemData['is_published'],
                'is_active' => $itemData['is_active'],
            ]);

            foreach ($fieldValues as $fv) {
                $idItemFieldValue = Util::uuid();
                $stmtFv = $this->db->prepare('
                    insert into item_field_value (
                        id_item_field_value,
                        id_item,
                        id_category_field,
                        field_value_text,
                        field_value_number,
                        field_value_boolean,
                        field_value_date,
                        field_value_option
                    ) values (
                        :id_item_field_value,
                        :id_item,
                        :id_category_field,
                        :field_value_text,
                        :field_value_number,
                        :field_value_boolean,
                        :field_value_date,
                        :field_value_option
                    )
                ');
                $stmtFv->execute([
                    'id_item_field_value' => $idItemFieldValue,
                    'id_item' => $idItem,
                    'id_category_field' => $fv['id_category_field'],
                    'field_value_text' => $fv['field_value_text'],
                    'field_value_number' => $fv['field_value_number'],
                    'field_value_boolean' => $fv['field_value_boolean'],
                    'field_value_date' => $fv['field_value_date'],
                    'field_value_option' => $fv['field_value_option'],
                ]);
            }

            $this->db->commit();

            return [
                'id_item' => $idItem,
                'seo_slug' => $itemData['seo_slug'],
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateItemWithFields(string $idItem, array $itemData, array $fieldValues): array
    {
        $this->db->beginTransaction();
        try {
            $sql = '
                update item set
                    id_category = :id_category,
                    title = :title,
                    short_description = :short_description,
                    description = :description,
                    listing_type = :listing_type,
                    item_status = :item_status,
                    start_price = :start_price,
                    fixed_price = :fixed_price,
                    bid_start_utc = :bid_start_utc,
                    bid_end_utc = :bid_end_utc,
                    currency_code = :currency_code,
                    location_text = :location_text,
                    meta_title = :meta_title,
                    meta_description = :meta_description,
                    is_published = :is_published,
                    is_active = :is_active,
                    date_updated_utc = now()
                where id_item = :id_item
                  and id_user = :id_user
            ';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_item' => $idItem,
                'id_user' => $itemData['id_user'],
                'id_category' => $itemData['id_category'],
                'title' => $itemData['title'],
                'short_description' => $itemData['short_description'],
                'description' => $itemData['description'],
                'listing_type' => $itemData['listing_type'],
                'item_status' => $itemData['item_status'],
                'start_price' => $itemData['start_price'],
                'fixed_price' => $itemData['fixed_price'],
                'bid_start_utc' => $itemData['bid_start_utc'],
                'bid_end_utc' => $itemData['bid_end_utc'],
                'currency_code' => $itemData['currency_code'],
                'location_text' => $itemData['location_text'],
                'meta_title' => $itemData['meta_title'],
                'meta_description' => $itemData['meta_description'],
                'is_published' => $itemData['is_published'],
                'is_active' => $itemData['is_active'],
            ]);

            $del = $this->db->prepare('delete from item_field_value where id_item = :id_item');
            $del->execute(['id_item' => $idItem]);

            foreach ($fieldValues as $fv) {
                $idItemFieldValue = Util::uuid();
                $stmtFv = $this->db->prepare('
                    insert into item_field_value (
                        id_item_field_value,
                        id_item,
                        id_category_field,
                        field_value_text,
                        field_value_number,
                        field_value_boolean,
                        field_value_date,
                        field_value_option
                    ) values (
                        :id_item_field_value,
                        :id_item,
                        :id_category_field,
                        :field_value_text,
                        :field_value_number,
                        :field_value_boolean,
                        :field_value_date,
                        :field_value_option
                    )
                ');
                $stmtFv->execute([
                    'id_item_field_value' => $idItemFieldValue,
                    'id_item' => $idItem,
                    'id_category_field' => $fv['id_category_field'],
                    'field_value_text' => $fv['field_value_text'],
                    'field_value_number' => $fv['field_value_number'],
                    'field_value_boolean' => $fv['field_value_boolean'],
                    'field_value_date' => $fv['field_value_date'],
                    'field_value_option' => $fv['field_value_option'],
                ]);
            }

            $this->db->commit();

            return [
                'id_item' => $idItem,
                'seo_slug' => $itemData['seo_slug'],
            ];
        } catch (Throwable $e) {
       	    $this->db->rollBack();
            throw $e;
        }
    }
}

