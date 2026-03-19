<?php

class CategoryRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getActiveCategories(): array
    {
        $stmt = $this->db->query('
            select id_category, category_name, category_code, description, display_order
            from category
            where is_active = true
            order by display_order nulls last, category_name
        ');
        return $stmt->fetchAll();
    }

    public function getActiveFieldsWithOptions(string $idCategory): array
    {
        $sql = '
            select
                cf.id_category_field,
                cf.field_name,
                cf.field_label,
                cf.field_type,
                cf.is_required,
                cf.is_filterable,
                cf.display_order,
                cfo.id_category_field_option,
                cfo.option_value,
                cfo.option_label,
                cfo.display_order as option_display_order
            from category_field cf
            left join category_field_option cfo
                on cfo.id_category_field = cf.id_category_field
               and cfo.is_active = true
            where cf.id_category = :id_category
              and cf.is_active = true
            order by cf.display_order nulls last, cf.date_created_utc, cfo.display_order nulls last, cfo.option_label
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_category' => $idCategory]);
        $rows = $stmt->fetchAll();

        $fields = [];
        foreach ($rows as $row) {
            $fieldId = $row['id_category_field'];
            if (!isset($fields[$fieldId])) {
                $fields[$fieldId] = [
                    'id_category_field' => $row['id_category_field'],
                    'field_name' => $row['field_name'],
                    'field_label' => $row['field_label'],
                    'field_type' => $row['field_type'],
                    'is_required' => (bool) $row['is_required'],
                    'is_filterable' => (bool) $row['is_filterable'],
                    'display_order' => $row['display_order'],
                    'options' => [],
                ];
            }

            if ($row['field_type'] === 'select' && $row['id_category_field_option']) {
                $fields[$fieldId]['options'][] = [
                    'id_category_field_option' => $row['id_category_field_option'],
                    'option_value' => $row['option_value'],
                    'option_label' => $row['option_label'],
                    'display_order' => $row['option_display_order'],
                ];
            }
        }

        return array_values($fields);
    }
}

