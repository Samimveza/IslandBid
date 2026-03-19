<?php

class BidRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function placeBid(array $user, string $idItem, float $bidAmount): array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('
                select id_item, id_user, listing_type, item_status, is_published, bid_end_utc, current_highest_bid, start_price
                from item
                where id_item = :id_item
                for update
            ');
            $stmt->execute(['id_item' => $idItem]);
            $item = $stmt->fetch();

            if (!$item) {
                JsonResponse::error('Item not found.', 404);
            }
            if ($item['item_status'] !== 'active' || !(bool) $item['is_published']) {
                JsonResponse::error('Item is not open for bidding.', 422);
            }
            if (!in_array($item['listing_type'], ['bid', 'both'], true)) {
                JsonResponse::error('This listing does not allow bidding.', 422);
            }
            if (!empty($item['bid_end_utc']) && strtotime((string) $item['bid_end_utc']) <= time()) {
                JsonResponse::error('Bidding has ended for this item.', 422);
            }
            if ($item['id_user'] === $user['id_user']) {
                JsonResponse::error('You cannot bid on your own item.', 422);
            }

            $base = $item['current_highest_bid'] !== null ? (float) $item['current_highest_bid'] : (float) ($item['start_price'] ?? 0);
            if ($bidAmount <= $base) {
                JsonResponse::error('Bid amount must be greater than current highest bid.', 422, [
                    'minimum_required' => $base + 0.01,
                ]);
            }

            $idBid = Util::uuid();
            $insert = $this->db->prepare('
                insert into bid (id_bid, id_item, id_user, bid_amount, bid_status)
                values (:id_bid, :id_item, :id_user, :bid_amount, :bid_status)
            ');
            $insert->execute([
                'id_bid' => $idBid,
                'id_item' => $idItem,
                'id_user' => $user['id_user'],
                'bid_amount' => $bidAmount,
                'bid_status' => 'active',
            ]);

            $update = $this->db->prepare('
                update item
                set current_highest_bid = :current_highest_bid,
                    date_updated_utc = now()
                where id_item = :id_item
            ');
            $update->execute([
                'current_highest_bid' => $bidAmount,
                'id_item' => $idItem,
            ]);

            $this->db->commit();

            return [
                'id_bid' => $idBid,
                'id_item' => $idItem,
                'bid_amount' => $bidAmount,
                'current_highest_bid' => $bidAmount,
                'bid_status' => 'active',
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateOwnBid(array $user, string $idItem, float $bidAmount): array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('
                select id_item, id_user, listing_type, item_status, is_published, bid_end_utc, current_highest_bid
                from item
                where id_item = :id_item
                for update
            ');
            $stmt->execute(['id_item' => $idItem]);
            $item = $stmt->fetch();

            if (!$item) {
                JsonResponse::error('Item not found.', 404);
            }
            if ($item['item_status'] !== 'active') {
                JsonResponse::error('Item is not open for bid updates.', 422);
            }
            if (!(bool) $item['is_published']) {
                JsonResponse::error('Item is not published.', 422);
            }
            if (!in_array($item['listing_type'], ['bid', 'both'], true)) {
                JsonResponse::error('This listing does not allow bidding.', 422);
            }
            if (!empty($item['bid_end_utc']) && strtotime((string) $item['bid_end_utc']) <= time()) {
                JsonResponse::error('Bidding has ended for this item.', 422);
            }
            if ($item['id_user'] === $user['id_user']) {
                JsonResponse::error('You cannot update a bid on your own item.', 422);
            }

            $myBidStmt = $this->db->prepare('
                select id_bid, bid_amount
                from bid
                where id_item = :id_item
                  and id_user = :id_user
                  and bid_status = :bid_status
                order by bid_time_utc desc
                limit 1
                for update
            ');
            $myBidStmt->execute([
                'id_item' => $idItem,
                'id_user' => $user['id_user'],
                'bid_status' => 'active',
            ]);
            $myBid = $myBidStmt->fetch();
            if (!$myBid) {
                JsonResponse::error('No active bid found to update.', 422);
            }

            $currentHighest = (float) ($item['current_highest_bid'] ?? 0);
            if ($bidAmount <= $currentHighest) {
                JsonResponse::error('Updated bid must be greater than current highest bid.', 422, [
                    'minimum_required' => $currentHighest + 0.01,
                ]);
            }

            $mark = $this->db->prepare('
                update bid
                set bid_status = :new_status,
                    date_updated_utc = now()
                where id_bid = :id_bid
            ');
            $mark->execute([
                'new_status' => 'updated',
                'id_bid' => $myBid['id_bid'],
            ]);

            $idBid = Util::uuid();
            $insert = $this->db->prepare('
                insert into bid (id_bid, id_item, id_user, bid_amount, bid_status)
                values (:id_bid, :id_item, :id_user, :bid_amount, :bid_status)
            ');
            $insert->execute([
                'id_bid' => $idBid,
                'id_item' => $idItem,
                'id_user' => $user['id_user'],
                'bid_amount' => $bidAmount,
                'bid_status' => 'active',
            ]);

            $recalc = $this->db->prepare('
                select max(bid_amount) as highest
                from bid
                where id_item = :id_item
                  and bid_status in (\'active\', \'updated\', \'won\', \'lost\')
            ');
            $recalc->execute(['id_item' => $idItem]);
            $highest = (float) ($recalc->fetchColumn() ?: 0);

            $updateItem = $this->db->prepare('
                update item
                set current_highest_bid = :current_highest_bid,
                    date_updated_utc = now()
                where id_item = :id_item
            ');
            $updateItem->execute([
                'current_highest_bid' => $highest,
                'id_item' => $idItem,
            ]);

            $this->db->commit();

            return [
                'id_bid' => $idBid,
                'id_item' => $idItem,
                'previous_bid_id' => $myBid['id_bid'],
                'bid_amount' => $bidAmount,
                'current_highest_bid' => $highest,
                'bid_status' => 'active',
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function removeOwnBid(array $user, string $idItem): array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('
                select id_item, id_user, item_status, is_published, bid_end_utc
                from item
                where id_item = :id_item
                for update
            ');
            $stmt->execute(['id_item' => $idItem]);
            $item = $stmt->fetch();

            if (!$item) {
                JsonResponse::error('Item not found.', 404);
            }
            if (!(bool) $item['is_published']) {
                JsonResponse::error('Item is not published.', 422);
            }
            if (in_array($item['item_status'], ['sold', 'expired', 'cancelled'], true)) {
                JsonResponse::error('Bid cannot be removed for this item status.', 422);
            }
            if (!empty($item['bid_end_utc']) && strtotime((string) $item['bid_end_utc']) <= time()) {
                JsonResponse::error('Bidding has ended for this item.', 422);
            }
            if ($item['id_user'] === $user['id_user']) {
                JsonResponse::error('Owners do not have bids to remove on own item.', 422);
            }

            $myBidStmt = $this->db->prepare('
                select id_bid
                from bid
                where id_item = :id_item
                  and id_user = :id_user
                  and bid_status = :bid_status
                order by bid_time_utc desc
                limit 1
                for update
            ');
            $myBidStmt->execute([
                'id_item' => $idItem,
                'id_user' => $user['id_user'],
                'bid_status' => 'active',
            ]);
            $myBid = $myBidStmt->fetch();
            if (!$myBid) {
                JsonResponse::error('No active bid found to remove.', 422);
            }

            $mark = $this->db->prepare('
                update bid
                set bid_status = :new_status,
                    date_updated_utc = now()
                where id_bid = :id_bid
            ');
            $mark->execute([
                'new_status' => 'removed',
                'id_bid' => $myBid['id_bid'],
            ]);

            $recalc = $this->db->prepare('
                select max(bid_amount) as highest
                from bid
                where id_item = :id_item
                  and bid_status in (\'active\', \'updated\', \'won\', \'lost\')
            ');
            $recalc->execute(['id_item' => $idItem]);
            $highest = $recalc->fetchColumn();
            $highest = $highest !== false ? (float) $highest : null;

            $updateItem = $this->db->prepare('
                update item
                set current_highest_bid = :current_highest_bid,
                    date_updated_utc = now()
                where id_item = :id_item
            ');
            $updateItem->bindValue(':current_highest_bid', $highest, $highest === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $updateItem->bindValue(':id_item', $idItem);
            $updateItem->execute();

            $this->db->commit();

            return [
                'id_item' => $idItem,
                'removed_bid_id' => $myBid['id_bid'],
                'current_highest_bid' => $highest,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

