<?php

class ItemService
{
    public function __construct(private ItemRepository $items)
    {
    }

    public function getItemDetailsBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $item = $this->items->findBySlug($slug);
        if (!$item) {
            return null;
        }

        $this->items->incrementViewCount($item['id_item']);

        $images = $this->items->getImages($item['id_item']);
        $fields = $this->items->getCategoryFieldsWithValues($item['id_item'], $item['id_category']);
        $bids = $this->items->getBids($item['id_item']);

        return [
            'item' => $item,
            'images' => $images,
            'fields' => $fields,
            'bids' => $bids,
        ];
    }

    public function createItem(array $user, array $payload, array $categoryFields): array
    {
        $errors = Validator::required($payload, ['title', 'description', 'id_category', 'listing_type', 'item_status']);
        if ($errors) {
            JsonResponse::error('Validation failed.', 422, $errors);
        }

        $listingType = $payload['listing_type'];
        if (!Validator::inArray($listingType, ['bid', 'fixed_price', 'both'])) {
            JsonResponse::error('Invalid listing type.', 422);
        }

        $itemStatus = $payload['item_status'];
        if (!Validator::inArray($itemStatus, ['draft', 'active', 'sold', 'expired', 'cancelled'])) {
            JsonResponse::error('Invalid item status.', 422);
        }

        $isPublished = !empty($payload['is_published']);
        $isActive = $itemStatus !== 'cancelled';

        $startPrice = array_key_exists('start_price', $payload) && $payload['start_price'] !== ''
            ? (float) $payload['start_price']
            : null;
        $fixedPrice = array_key_exists('fixed_price', $payload) && $payload['fixed_price'] !== ''
            ? (float) $payload['fixed_price']
            : null;

        if ($itemStatus === 'active') {
            if ($listingType === 'bid' && $startPrice === null) {
                JsonResponse::error('Start price is required for bidding listings.', 422);
            }
            if ($listingType === 'fixed_price' && $fixedPrice === null) {
                JsonResponse::error('Fixed price is required for fixed-price listings.', 422);
            }
            if ($listingType === 'both' && $startPrice === null && $fixedPrice === null) {
                JsonResponse::error('At least one price is required for combined listings.', 422);
            }
        }

        $slugSource = ($payload['seo_slug'] ?? '') !== '' ? $payload['seo_slug'] : $payload['title'];
        $baseSlug = Slug::make($slugSource);
        $seoSlug = $baseSlug;
        $suffix = 2;
        while ($this->items->slugExists($seoSlug)) {
            $seoSlug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $itemData = [
            'id_user' => $user['id_user'],
            'id_category' => $payload['id_category'],
            'title' => trim((string) $payload['title']),
            'short_description' => trim((string) ($payload['short_description'] ?? '')),
            'description' => trim((string) $payload['description']),
            'listing_type' => $listingType,
            'item_status' => $itemStatus,
            'start_price' => $startPrice,
            'fixed_price' => $fixedPrice,
            'bid_start_utc' => ($payload['bid_start_utc'] ?? '') !== '' ? $payload['bid_start_utc'] : null,
            'bid_end_utc' => ($payload['bid_end_utc'] ?? '') !== '' ? $payload['bid_end_utc'] : null,
            'currency_code' => ($payload['currency_code'] ?? '') !== '' ? $payload['currency_code'] : 'MUR',
            'location_text' => trim((string) ($payload['location_text'] ?? '')),
            'seo_slug' => $seoSlug,
            'meta_title' => trim((string) ($payload['meta_title'] ?? '')),
            'meta_description' => trim((string) ($payload['meta_description'] ?? '')),
            'is_published' => $isPublished ? 1 : 0,
            'is_active' => $isActive ? 1 : 0,
        ];

        $dynamicValues = $payload['fields'] ?? [];
        $fieldValues = [];
        $dynamicErrors = [];

        foreach ($categoryFields as $field) {
            $fieldId = $field['id_category_field'];
            $key = $fieldId;
            $value = $dynamicValues[$key] ?? null;

            if ($field['is_required'] && ($value === null || $value === '')) {
                $dynamicErrors[$key] = 'This field is required.';
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $row = [
                'id_category_field' => $fieldId,
                'field_value_text' => null,
                'field_value_number' => null,
                'field_value_boolean' => null,
                'field_value_date' => null,
                'field_value_option' => null,
            ];

            switch ($field['field_type']) {
                case 'text':
                    $row['field_value_text'] = (string) $value;
                    break;
                case 'number':
                case 'decimal':
                    $row['field_value_number'] = (float) $value;
                    break;
                case 'boolean':
                    $row['field_value_boolean'] = (bool) $value;
                    break;
                case 'date':
                    $row['field_value_date'] = $value;
                    break;
                case 'select':
                    $row['field_value_option'] = (string) $value;
                    break;
            }

            $fieldValues[] = $row;
        }

        if ($dynamicErrors) {
            JsonResponse::error('Validation failed.', 422, ['fields' => $dynamicErrors]);
        }

        return $this->items->createItemWithFields($itemData, $fieldValues);
    }
}

