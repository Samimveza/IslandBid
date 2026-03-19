<?php

class SellerDashboardRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getListings(string $idUser, ?string $status = null): array
    {
        $where = ['i.id_user = :id_user'];
        $params = ['id_user' => $idUser];

        if ($status !== null && $status !== '') {
            $where[] = 'i.item_status = :item_status';
            $params['item_status'] = $status;
        }

        $whereSql = implode(' and ', $where);

        $sql = "
            select
                i.id_item,
                i.title,
                i.seo_slug,
                i.listing_type,
                i.item_status,
                i.current_highest_bid,
                i.fixed_price,
                i.bid_end_utc,
                i.date_created_utc,
                c.category_name,
                img.server_file_path
            from item i
            join category c on c.id_category = i.id_category
            left join lateral (
                select (rtrim(p.paramater_value, '/') || '/' || ltrim(d.server_file_path, '/')) as server_file_path
                from item_document idoc
                join document d on d.id_document = idoc.id_document
                join parameter p on p.id_parameter = d.id_parameter_base_server_url
                where idoc.id_item = i.id_item
                  and d.is_deactivated = false
                order by idoc.is_primary desc, idoc.display_order nulls last, d.date_created_utc desc
                limit 1
            ) img on true
            where {$whereSql}
            order by i.date_created_utc desc
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

