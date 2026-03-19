<?php

require_once __DIR__ . '/../src/helpers/SessionAuth.php';
SessionAuth::start();
$currentUser = SessionAuth::user();
if (!$currentUser) {
    header('Location: /login');
    exit;
}
?>
<!doctype html>
<html lang="en" ng-app="islandBidApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - IslandBid</title>
    <meta name="description" content="View your active bids, won, lost, and saved items.">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body class="buyer-page" ng-controller="BuyerDashboardController as vm">
    <header class="topbar">
        <div class="container">
            <h1>IslandBid</h1>
            <div class="auth-strip">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['first_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    </header>

    <main class="container buyer-layout">
        <h2>Buyer Dashboard</h2>

        <section class="buyer-section">
            <h3>Active bids</h3>
            <div class="buyer-list" ng-if="vm.active_bids.length">
                <article class="buyer-row" ng-repeat="item in vm.active_bids track by item.id_item">
                    <div class="buyer-img">
                        <img ng-if="item.server_file_path" ng-src="{{ item.server_file_path }}" alt="{{ item.title }}">
                        <div class="placeholder" ng-if="!item.server_file_path">No image</div>
                    </div>
                    <div class="buyer-info">
                        <h4><a ng-href="/item/{{ item.seo_slug }}">{{ item.title }}</a></h4>
                        <div class="buyer-meta">
                            <span>{{ item.category_name }}</span>
                            <span>Status: {{ item.item_status }}</span>
                        </div>
                        <div class="buyer-prices">
                            <span>Your bid: MUR {{ item.bid_amount | number:0 }}</span>
                            <span>Current highest: MUR {{ item.current_highest_bid | number:0 }}</span>
                            <span ng-if="item.bid_end_utc">Ends: {{ item.bid_end_utc | date:'medium' }}</span>
                        </div>
                    </div>
                </article>
            </div>
            <p class="empty-state" ng-if="!vm.active_bids.length">No active bids.</p>
        </section>

        <section class="buyer-section">
            <h3>Won items</h3>
            <div class="buyer-list" ng-if="vm.won_items.length">
                <article class="buyer-row" ng-repeat="item in vm.won_items track by item.id_item">
                    <div class="buyer-img">
                        <img ng-if="item.server_file_path" ng-src="{{ item.server_file_path }}" alt="{{ item.title }}">
                        <div class="placeholder" ng-if="!item.server_file_path">No image</div>
                    </div>
                    <div class="buyer-info">
                        <h4><a ng-href="/item/{{ item.seo_slug }}">{{ item.title }}</a></h4>
                        <div class="buyer-meta">
                            <span>{{ item.category_name }}</span>
                            <span>Status: {{ item.item_status }}</span>
                        </div>
                        <div class="buyer-prices">
                            <span>Your bid: MUR {{ item.bid_amount | number:0 }}</span>
                            <span>Final highest: MUR {{ item.current_highest_bid | number:0 }}</span>
                        </div>
                    </div>
                </article>
            </div>
            <p class="empty-state" ng-if="!vm.won_items.length">No won items yet.</p>
        </section>

        <section class="buyer-section">
            <h3>Lost items</h3>
            <div class="buyer-list" ng-if="vm.lost_items.length">
                <article class="buyer-row" ng-repeat="item in vm.lost_items track by item.id_item">
                    <div class="buyer-img">
                        <img ng-if="item.server_file_path" ng-src="{{ item.server_file_path }}" alt="{{ item.title }}">
                        <div class="placeholder" ng-if="!item.server_file_path">No image</div>
                    </div>
                    <div class="buyer-info">
                        <h4><a ng-href="/item/{{ item.seo_slug }}">{{ item.title }}</a></h4>
                        <div class="buyer-meta">
                            <span>{{ item.category_name }}</span>
                            <span>Status: {{ item.item_status }}</span>
                        </div>
                        <div class="buyer-prices">
                            <span>Your bid: MUR {{ item.bid_amount | number:0 }}</span>
                            <span>Final highest: MUR {{ item.current_highest_bid | number:0 }}</span>
                        </div>
                    </div>
                </article>
            </div>
            <p class="empty-state" ng-if="!vm.lost_items.length">No lost items.</p>
        </section>

        <section class="buyer-section">
            <h3>Saved items</h3>
            <div class="buyer-list" ng-if="vm.saved_items.length">
                <article class="buyer-row" ng-repeat="item in vm.saved_items track by item.id_item">
                    <div class="buyer-img">
                        <img ng-if="item.server_file_path" ng-src="{{ item.server_file_path }}" alt="{{ item.title }}">
                        <div class="placeholder" ng-if="!item.server_file_path">No image</div>
                    </div>
                    <div class="buyer-info">
                        <h4><a ng-href="/item/{{ item.seo_slug }}">{{ item.title }}</a></h4>
                        <div class="buyer-meta">
                            <span>{{ item.category_name }}</span>
                            <span>Status: {{ item.item_status }}</span>
                        </div>
                        <div class="buyer-prices">
                            <span ng-if="item.current_highest_bid">Current highest: MUR {{ item.current_highest_bid | number:0 }}</span>
                            <span ng-if="item.fixed_price">Fixed: MUR {{ item.fixed_price | number:0 }}</span>
                        </div>
                    </div>
                </article>
            </div>
            <p class="empty-state" ng-if="!vm.saved_items.length">No saved items.</p>
        </section>
    </main>

    <script>
        window.__APP_BOOTSTRAP__ = { user: <?php echo json_encode($currentUser); ?> };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script src="/frontend/app.js"></script>
    <script src="/frontend/services/api.service.js"></script>
    <script src="/frontend/services/auth.service.js"></script>
    <script src="/frontend/services/dashboard.service.js"></script>
    <script src="/frontend/controllers/buyer-dashboard.controller.js"></script>
</body>
</html>

