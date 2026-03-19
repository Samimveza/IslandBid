<?php

require_once __DIR__ . '/../src/helpers/SessionAuth.php';
require_once __DIR__ . '/../src/config/env.php';

SessionAuth::start();
$currentUser = SessionAuth::user();

$slug = (string) ($_GET['seo_slug'] ?? '');
$slug = trim($slug);

if ($slug === '') {
    http_response_code(404);
    echo 'Item not found.';
    exit;
}

$config = require __DIR__ . '/../src/config/config.php';
$baseUrl = rtrim($config['app']['url'], '/');
$apiUrl = $baseUrl . '/api/items/detail?slug=' . urlencode($slug);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_TIMEOUT => 5,
]);
$raw = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$raw) {
    http_response_code($httpCode ?: 404);
    echo 'Item not found.';
    exit;
}

$decoded = json_decode($raw, true);
if (!is_array($decoded) || empty($decoded['success']) || empty($decoded['data']['item'])) {
    http_response_code(404);
    echo 'Item not found.';
    exit;
}

$data = $decoded['data'];
$item = $data['item'];
$images = $data['images'] ?? [];
$fields = $data['fields'] ?? [];
$bids = $data['bids'] ?? [];

$metaTitle = $item['meta_title'] ?: $item['title'];
$metaDescription = $item['meta_description'] ?: ($item['short_description'] ?? '');
$canonical = $baseUrl . '/item/' . urlencode($item['seo_slug']);

?>
<!doctype html>
<html lang="en" ng-app="islandBidApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body class="item-page" ng-controller="ItemController as vm">
    <header class="topbar">
        <div class="container">
            <h1>IslandBid</h1>
            <div class="auth-strip" ng-if="!vm.user">
                <a href="/login" class="link-button">Login</a>
                <a href="/register" class="link-button secondary">Create account</a>
            </div>
            <div class="auth-strip" ng-if="vm.user">
                <span>Welcome, {{ vm.user.first_name }}</span>
                <button type="button" ng-click="vm.logout()">Logout</button>
            </div>
        </div>
    </header>

    <main class="container item-layout">
        <article class="item-details" itemscope itemtype="https://schema.org/Product">
            <header class="item-header">
                <h1 itemprop="name"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="item-header-meta">
                    <span class="chip"><?php echo htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if (!empty($item['location_text'])): ?>
                        <span class="chip chip-light"><?php echo htmlspecialchars($item['location_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <span class="chip chip-type">
                        <?php
                        echo htmlspecialchars(ucwords(str_replace('_', ' ', $item['listing_type'])), ENT_QUOTES, 'UTF-8');
                        ?>
                    </span>
                    <button type="button"
                            class="save-pill save-pill--inline"
                            ng-click="vm.toggleSaved()">
                        {{ vm.isSaved ? 'Saved' : 'Save' }}
                    </button>
                </div>
            </header>

            <section class="item-main">
                <div class="item-gallery">
                    <?php if ($images): ?>
                        <div class="item-gallery-main">
                            <?php
                            $mainImage = $images[0];
                            $src = $mainImage['server_file_path'] ?: '';
                            ?>
                            <?php if ($src): ?>
                                <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <div class="item-gallery-thumbs">
                                <?php foreach ($images as $img): ?>
                                    <?php if (empty($img['server_file_path'])) {
                                        continue;
                                    } ?>
                                    <img src="<?php echo htmlspecialchars($img['server_file_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="item-gallery-placeholder">No images available</div>
                    <?php endif; ?>
                </div>

                <aside class="item-sidebar">
                    <div class="price-panel">
                        <?php if (!empty($item['current_highest_bid'])): ?>
                            <div class="price-row">
                                <span class="label">Current bid</span>
                                <span class="value">MUR <?php echo number_format((float) $item['current_highest_bid'], 0); ?></span>
                            </div>
                        <?php elseif (!empty($item['start_price'])): ?>
                            <div class="price-row">
                                <span class="label">Start price</span>
                                <span class="value">MUR <?php echo number_format((float) $item['start_price'], 0); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['fixed_price'])): ?>
                            <div class="price-row">
                                <span class="label">Buy now</span>
                                <span class="value">MUR <?php echo number_format((float) $item['fixed_price'], 0); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['bid_start_utc']) || !empty($item['bid_end_utc'])): ?>
                            <div class="dates">
                                <?php if (!empty($item['bid_start_utc'])): ?>
                                    <div>Starts: <?php echo htmlspecialchars($item['bid_start_utc'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['bid_end_utc'])): ?>
                                    <div>Ends: <?php echo htmlspecialchars($item['bid_end_utc'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($currentUser): ?>
                            <div class="bid-box">
                                <div class="bid-current">
                                    Current highest: <strong>{{ vm.currentHighestBid | currency:'MUR ':0 }}</strong>
                                </div>
                                <div class="bid-form" ng-if="vm.canBid">
                                    <input type="number" min="0" step="1" placeholder="Enter your bid" ng-model="vm.bidAmount">
                                    <button type="button" class="btn-primary full-width" ng-if="!vm.myActiveBid" ng-disabled="vm.bidSubmitting" ng-click="vm.placeBid()">
                                        {{ vm.bidSubmitting ? 'Placing bid...' : 'Place bid' }}
                                    </button>
                                    <button type="button" class="btn-primary full-width" ng-if="vm.myActiveBid" ng-disabled="vm.bidSubmitting" ng-click="vm.updateBid()">
                                        {{ vm.bidSubmitting ? 'Updating bid...' : 'Update my bid' }}
                                    </button>
                                    <button type="button" class="btn-secondary full-width" ng-if="vm.myActiveBid" ng-disabled="vm.bidSubmitting" ng-click="vm.removeBid()">
                                        Remove my bid
                                    </button>
                                </div>
                                <p class="login-prompt" ng-if="!vm.canBid">You cannot bid on this item.</p>
                                <p class="bid-success" ng-if="vm.bidSuccess">{{ vm.bidSuccess }}</p>
                                <p class="bid-error" ng-if="vm.bidError">{{ vm.bidError }}</p>
                            </div>
                        <?php else: ?>
                            <p class="login-prompt">
                                <a href="/login">Login</a> or <a href="/register">create an account</a> to place a bid.
                            </p>
                        <?php endif; ?>
                    </div>

                    <section class="bids-panel">
                        <h2>Recent bids</h2>
                        <ul class="bids-list" ng-if="vm.bids.length">
                            <li ng-repeat="b in vm.bids">
                                <span class="bidder">
                                    {{ b.first_name }} {{ (b.last_name | limitTo:1) | uppercase }}.
                                </span>
                                <span class="amount">MUR {{ b.bid_amount | number:0 }}</span>
                                <span class="time">{{ b.bid_time_utc | date:'medium' }}</span>
                            </li>
                        </ul>
                        <p class="no-bids" ng-if="!vm.bids.length">No bids yet.</p>
                    </section>
                </aside>
            </section>

            <section class="item-content">
                <?php if (!empty($item['short_description'])): ?>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($item['short_description'], ENT_QUOTES, 'UTF-8')); ?></p>
                <?php endif; ?>

                <?php if (!empty($item['description'])): ?>
                    <div class="description" itemprop="description">
                        <?php echo nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php endif; ?>

                <?php if ($fields): ?>
                    <section class="specs">
                        <h2>Details</h2>
                        <dl>
                            <?php foreach ($fields as $field): ?>
                                <?php
                                $value = null;
                                switch ($field['field_type']) {
                                    case 'text':
                                    case 'date':
                                        $value = $field['field_value_text'] ?? $field['field_value_date'];
                                        break;
                                    case 'number':
                                    case 'decimal':
                                        $value = $field['field_value_number'];
                                        break;
                                    case 'boolean':
                                        if ($field['field_value_boolean'] !== null) {
                                            $value = $field['field_value_boolean'] ? 'Yes' : 'No';
                                        }
                                        break;
                                    case 'select':
                                        $value = $field['field_value_label'] ?? $field['field_value_option'];
                                        break;
                                }
                                if ($value === null || $value === '') {
                                    continue;
                                }
                                ?>
                                <div class="spec-row">
                                    <dt><?php echo htmlspecialchars($field['field_label'], ENT_QUOTES, 'UTF-8'); ?></dt>
                                    <dd><?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </section>
                <?php endif; ?>
            </section>
        </article>
    </main>

    <script>
        window.__APP_BOOTSTRAP__ = {
            user: <?php echo json_encode($currentUser); ?>,
            item: <?php echo json_encode([
                'id_item' => $item['id_item'],
                'seo_slug' => $item['seo_slug'],
                'id_user' => $item['id_user'],
                'listing_type' => $item['listing_type'],
                'current_highest_bid' => $item['current_highest_bid'],
                'start_price' => $item['start_price'],
                'bid_end_utc' => $item['bid_end_utc'],
                'is_published' => $item['is_published'],
                'item_status' => $item['item_status'],
            ]); ?>
        };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script src="/frontend/app.js"></script>
    <script src="/frontend/services/api.service.js"></script>
    <script src="/frontend/services/auth.service.js"></script>
    <script src="/frontend/services/saved.service.js"></script>
    <script src="/frontend/controllers/auth.controller.js"></script>
    <script src="/frontend/controllers/item.controller.js"></script>
</body>
</html>

