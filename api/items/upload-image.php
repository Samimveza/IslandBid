<?php

$user = AuthMiddleware::requireAuth();

$idItem = (string) ($_POST['id_item'] ?? '');
$idItem = trim($idItem);

if ($idItem === '') {
    JsonResponse::error('id_item is required.', 422);
}

if (empty($_FILES['file'])) {
    JsonResponse::error('No file uploaded.', 422);
}

$displayOrder = isset($_POST['display_order']) && $_POST['display_order'] !== ''
    ? (int) $_POST['display_order']
    : null;
$isPrimary = !empty($_POST['is_primary']);

$db = Database::connection();
$itemRepo = new ItemRepository($db);
$owned = $itemRepo->findOwnedItem($idItem, $user['id_user']);

if (!$owned) {
    JsonResponse::error('You are not allowed to modify this item.', 403);
}

$paramRepo = new ParameterRepository($db);
$docTypeRepo = new DocumentTypeRepository($db);
$docRepo = new DocumentRepository($db);

$physicalParam = $paramRepo->getByCode('BASE_PHYSICAL_PATH');
$serverParam = $paramRepo->getByCode('BASE_SERVER_URL');
$docType = $docTypeRepo->getByCode('ITEM_IMAGE');

if (!$physicalParam || !$serverParam || !$docType) {
    JsonResponse::error('Document configuration is missing.', 500);
}

try {
    $config = require __DIR__ . '/../../src/config/config.php';
    $physicalBase = rtrim($config['app']['uploads_physical'] ?? getenv('UPLOAD_PHYSICAL_BASE') ?? $physicalParam['paramater_value'], DIRECTORY_SEPARATOR);
    $serverFolder = rtrim($config['app']['uploads_folder'] ?? getenv('UPLOAD_SERVER_FOLDER') ?? '/uploads', '/');

    $meta = Upload::moveImage($_FILES['file'], $physicalBase);
    $meta['document_order'] = $displayOrder;
    $meta['physical_path'] = rtrim($physicalBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $meta['stored_name'];
    $meta['server_path'] = $serverFolder . '/' . $meta['stored_name'];

    $document = $docRepo->createImageDocument($meta, $docType, $physicalParam, $serverParam);
    $link = $docRepo->linkToItem($idItem, $document['id_document'], $displayOrder, $isPrimary);
} catch (RuntimeException $e) {
    JsonResponse::error($e->getMessage(), 422);
}

JsonResponse::success([
    'image' => array_merge($document, $link, [
        'full_server_url' => rtrim((string) $serverParam['paramater_value'], '/') . '/' . ltrim((string) $document['server_file_path'], '/'),
        'saved_physical_path' => $meta['physical_path'],
    ]),
]);

