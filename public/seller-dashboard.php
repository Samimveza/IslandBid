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
    <title>Seller Dashboard - IslandBid</title>
    <meta name="description" content="Manage your listings, statuses, bids and images.">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body class="seller-page" ng-controller="SellerDashboardController as vm">
    <header class="topbar">
        <div class="container">
            <h1>IslandBid</h1>
            <div class="auth-strip">
                <span>Welcome, <?php echo htmlspecialchars($currentUser['first_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="/create-item" class="btn-filter">Create Listing</a>
            </div>
        </div>
    </header>

    <main class="container seller-layout">
        <div class="seller-head">
            <h2>Seller Dashboard</h2>
            <div class="seller-filters">
                <button type="button" ng-class="{active: vm.status==='all'}" ng-click="vm.setStatus('all')">All</button>
                <button type="button" ng-class="{active: vm.status==='active'}" ng-click="vm.setStatus('active')">Active</button>
                <button type="button" ng-class="{active: vm.status==='sold'}" ng-click="vm.setStatus('sold')">Sold</button>
                <button type="button" ng-class="{active: vm.status==='expired'}" ng-click="vm.setStatus('expired')">Expired</button>
                <button type="button" ng-class="{active: vm.status==='draft'}" ng-click="vm.setStatus('draft')">Draft</button>
            </div>
        </div>

        <section class="seller-list" ng-if="vm.items.length">
            <article class="seller-row" ng-repeat="item in vm.items track by item.id_item">
                <div class="seller-img">
                    <img ng-if="item.server_file_path" ng-src="{{ item.server_file_path }}" alt="{{ item.title }}">
                    <div class="placeholder" ng-if="!item.server_file_path">No image</div>
                </div>
                <div class="seller-info">
                    <h3>{{ item.title }}</h3>
                    <div class="seller-meta">
                        <span>{{ item.category_name }}</span>
                        <span>{{ item.listing_type }}</span>
                        <span class="status-pill">{{ item.item_status }}</span>
                    </div>
                    <div class="seller-prices">
                        <span ng-if="item.current_highest_bid">Highest bid: MUR {{ item.current_highest_bid | number:0 }}</span>
                        <span ng-if="item.fixed_price">Fixed: MUR {{ item.fixed_price | number:0 }}</span>
                        <span ng-if="item.bid_end_utc">Ends: {{ item.bid_end_utc | date:'medium' }}</span>
                        <span>Created: {{ item.date_created_utc | date:'mediumDate' }}</span>
                    </div>
                </div>
                <div class="seller-actions">
                    <a ng-href="/create-item?id_item={{ item.id_item }}" class="btn-secondary">Edit / Images</a>
                    <a ng-href="/item/{{ item.seo_slug }}" class="btn-secondary">View Bids</a>
                </div>
            </article>
        </section>

        <section class="empty-state" ng-if="!vm.items.length">
            <p>No listings found for this status.</p>
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
    <script src="/frontend/controllers/seller-dashboard.controller.js"></script>
</body>
</html>

