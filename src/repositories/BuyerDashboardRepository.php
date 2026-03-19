<?php

class BuyerDashboardRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getActiveBids(string $idUser): array
    {
        $sql = "
            select
                i.id_item,
                i.title,
                i.seo_slug,
                i.item_status,
                i.current_highest_bid,
                i.fixed_price,
                i.bid_end_utc,
                c.category_name,
                ub.bid_amount,
                img.server_file_path
            from (
                select b1.*
                from bid b1
                where b1.id_user = :id_user
                  and b1.bid_status in ('active', 'updated')
                  and b1.bid_time_utc = (
                      select max(b2.bid_time_utc)
                      from bid b2
                      where b2.id_item = b1.id_item
                        and b2.id_user = b1.id_user
                  )
            ) ub
            join item i on i.id_item = ub.id_item
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
            where i.item_status = 'active'
              and i.is_published = true
              and i.is_active = true
            order by ub.bid_time_utc desc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_user' => $idUser]);
        return $stmt->fetchAll();
    }

    public function getWonItems(string $idUser): array
    {
        $sql = "
            select
                i.id_item,
                i.title,
                i.seo_slug,
                i.item_status,
                i.current_highest_bid,
                i.fixed_price,
                i.bid_end_utc,
                c.category_name,
                b.bid_amount,
                img.server_file_path
            from bid b
            join item i on i.id_item = b.id_item
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
            where b.id_user = :id_user
              and b.bid_status = 'won'
            order by b.bid_time_utc desc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_user' => $idUser]);
        return $stmt->fetchAll();
    }

    public function getLostItems(string $idUser): array
    {
        $sql = "
            select
                i.id_item,
                i.title,
                i.seo_slug,
                i.item_status,
                i.current_highest_bid,
                i.fixed_price,
                i.bid_end_utc,
                c.category_name,
                b.bid_amount,
                img.server_file_path
            from bid b
            join item i on i.id_item = b.id_item
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
            where b.id_user = :id_user
              and b.bid_status = 'lost'
            order by b.bid_time_utc desc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_user' => $idUser]);
        return $stmt->fetchAll();
    }

    public function getSavedItems(string $idUser): array
    {
        $sql = "
            select
                i.id_item,
                i.title,
                i.seo_slug,
                i.item_status,
                i.current_highest_bid,
                i.fixed_price,
                i.bid_end_utc,
                c.category_name,
                img.server_file_path
            from saved_item s
            join item i on i.id_item = s.id_item
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
            where s.id_user = :id_user
            order by s.date_created_utc desc
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_user' => $idUser]);
        return $stmt->fetchAll();
    }
}

