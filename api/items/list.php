<?php

$db = Database::connection();

$page = max(1, (int) ($_GET['page'] ?? 1));
$pageSize = max(1, min(48, (int) ($_GET['page_size'] ?? 12)));
$offset = ($page - 1) * $pageSize;

$params = [];
$wheres = [
    "i.item_status = 'active'",
    'i.is_published = true',
    'i.is_active = true',
];

$keyword = trim((string) ($_GET['q'] ?? ''));
if ($keyword !== '') {
    $wheres[] = '(i.title ILIKE :kw OR i.short_description ILIKE :kw OR i.location_text ILIKE :kw OR c.category_name ILIKE :kw)';
    $params['kw'] = '%' . $keyword . '%';
}

$categoryId = trim((string) ($_GET['category_id'] ?? ''));
if ($categoryId !== '') {
    $wheres[] = 'i.id_category = :category_id';
    $params['category_id'] = $categoryId;
}

$listingType = trim((string) ($_GET['listing_type'] ?? ''));
if ($listingType !== '' && in_array($listingType, ['bid', 'fixed_price', 'both'], true)) {
    $wheres[] = 'i.listing_type = :listing_type';
    $params['listing_type'] = $listingType;
}

$whereSql = implode(' AND ', $wheres);

$sort = $_GET['sort'] ?? 'newest';
switch ($sort) {
    case 'ending_soon':
        $orderBy = 'i.bid_end_utc ASC NULLS LAST, i.date_created_utc DESC';
        break;
    case 'price_low':
        $orderBy = 'COALESCE(i.fixed_price, i.start_price) ASC NULLS LAST, i.date_created_utc DESC';
        break;
    case 'price_high':
        $orderBy = 'COALESCE(i.fixed_price, i.start_price) DESC NULLS LAST, i.date_created_utc DESC';
        break;
    default:
        $orderBy = 'i.date_created_utc DESC';
        break;
}

// Total count
$countSql = "
    select count(*) as total
    from item i
    join category c on c.id_category = i.id_category
    where {$whereSql}
";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

// Items with main image
$sql = "
    select
        i.id_item,
        i.title,
        i.seo_slug,
        i.listing_type,
        i.current_highest_bid,
        i.start_price,
        i.fixed_price,
        i.short_description,
        i.location_text,
        i.bid_end_utc,
        i.date_created_utc,
        c.category_name,
        img.server_file_path,
        img.physical_file_path
    from item i
    join category c on c.id_category = i.id_category
    left join lateral (
        select d.server_file_path, d.physical_file_path
        from item_document idoc
        join document d on d.id_document = idoc.id_document
        where idoc.id_item = i.id_item
          and d.is_deactivated = false
        order by idoc.is_primary desc, idoc.display_order nulls last, d.date_created_utc desc
        limit 1
    ) img on true
    where {$whereSql}
    order by {$orderBy}
    limit :limit offset :offset
";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

JsonResponse::success([
    'items' => $items,
    'pagination' => [
        'page' => $page,
        'page_size' => $pageSize,
        'total' => $total,
        'total_pages' => $pageSize > 0 ? (int) ceil($total / $pageSize) : 1,
    ],
]);
