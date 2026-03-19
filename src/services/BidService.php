<?php

class BidService
{
    public function __construct(private BidRepository $bids)
    {
    }

    public function place(array $user, array $payload): array
    {
        $idItem = trim((string) ($payload['id_item'] ?? ''));
        if ($idItem === '') {
            JsonResponse::error('id_item is required.', 422);
        }

        if (!isset($payload['bid_amount']) || $payload['bid_amount'] === '') {
            JsonResponse::error('bid_amount is required.', 422);
        }

        $bidAmount = (float) $payload['bid_amount'];
        if ($bidAmount <= 0) {
            JsonResponse::error('bid_amount must be greater than 0.', 422);
        }

        return $this->bids->placeBid($user, $idItem, $bidAmount);
    }

    public function update(array $user, array $payload): array
    {
        $idItem = trim((string) ($payload['id_item'] ?? ''));
        if ($idItem === '') {
            JsonResponse::error('id_item is required.', 422);
        }

        if (!isset($payload['bid_amount']) || $payload['bid_amount'] === '') {
            JsonResponse::error('bid_amount is required.', 422);
        }

        $bidAmount = (float) $payload['bid_amount'];
        if ($bidAmount <= 0) {
            JsonResponse::error('bid_amount must be greater than 0.', 422);
        }

        return $this->bids->updateOwnBid($user, $idItem, $bidAmount);
    }

    public function remove(array $user, array $payload): array
    {
        $idItem = trim((string) ($payload['id_item'] ?? ''));
        if ($idItem === '') {
            JsonResponse::error('id_item is required.', 422);
        }

        return $this->bids->removeOwnBid($user, $idItem);
    }
}

