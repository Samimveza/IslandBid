<?php

require_once __DIR__ . '/../src/helpers/SessionAuth.php';
SessionAuth::start();
$currentUser = SessionAuth::user();
?>

<html lang="en" ng-app="islandBidApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IslandBid - Premium Marketplace</title>
    <meta name="description" content="Discover premium listings with bidding and fixed-price options.">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body ng-controller="AuthController as auth">
    <header class="topbar">
        <div class="container">
            <h1>IslandBid</h1>
            <div class="auth-strip" ng-if="!auth.user">
                <a href="/login" class="link-button">Login</a>
                <a href="/register" class="link-button secondary">Create account</a>
            </div>
            <div class="auth-strip" ng-if="auth.user">
                <span>Welcome, {{ auth.user.first_name }}</span>
                <a href="/buyer-dashboard" class="link-button">Buyer Dashboard</a>
                <a href="/seller-dashboard" class="link-button">Seller Dashboard</a>
                <a href="/create-item" class="link-button secondary">Create Listing</a>
                <button type="button" ng-click="auth.logout()">Logout</button>
            </div>
        </div>
    </header>

    <main class="container landing" ng-controller="HomeController as home">
        <section class="filters-bar">
            <input type="text" ng-model="home.filters.q" placeholder="Search by keyword..." ng-keypress="$event.which === 13 && home.search()">

            <select ng-model="home.filters.listing_type" ng-change="home.applyListingType(home.filters.listing_type)">
                <option value="">All listing types</option>
                <option value="bid">Bidding only</option>
                <option value="fixed_price">Fixed price</option>
                <option value="both">Bid or buy now</option>
            </select>

            <select ng-model="home.filters.sort" ng-change="home.applySort(home.filters.sort)">
                <option value="newest">Newest</option>
                <option value="ending_soon">Ending soon</option>
                <option value="price_low">Price: low to high</option>
                <option value="price_high">Price: high to low</option>
            </select>

            <button type="button" class="btn-filter" ng-click="home.search()">Search</button>
        </section>

        <section class="items-grid" ng-if="home.items.length">
            <article class="item-card" ng-repeat="item in home.items track by item.id_item">
                <div class="item-image" ng-class="{ 'item-image--placeholder': !item.server_file_path }">
                    <img ng-if="item.server_file_path" ng-src="{{ item.server_file_path }}" alt="{{ item.title }}">
                    <div class="placeholder" ng-if="!item.server_file_path">No image</div>
                    <span class="badge badge-type">{{ item.listing_type === 'bid' ? 'Bid' : (item.listing_type === 'fixed_price' ? 'Fixed price' : 'Bid & Buy') }}</span>
                    <button type="button"
                            class="save-pill"
                            ng-class="{ 'save-pill--active': item.__saved }"
                            ng-click="home.toggleSaved(item, $event)">
                        {{ item.__saved ? 'Saved' : 'Save' }}
                    </button>
                </div>
                <div class="item-body">
                    <div class="item-meta">
                        <span class="category">{{ item.category_name }}</span>
                        <span class="location" ng-if="item.location_text">{{ item.location_text }}</span>
                    </div>
                    <h2 class="item-title">
                        <a ng-href="/item/{{ item.seo_slug }}">{{ item.title }}</a>
                    </h2>
                    <p class="item-description" ng-if="item.short_description">{{ item.short_description }}</p>
                    <div class="price-row">
                        <div class="price-block" ng-if="item.current_highest_bid">
                            <span class="label">Current bid</span>
                            <span class="value">{{ item.current_highest_bid | currency:'MUR ':0 }}</span>
                        </div>
                        <div class="price-block" ng-if="item.fixed_price">
                            <span class="label">Buy now</span>
                            <span class="value">{{ item.fixed_price | currency:'MUR ':0 }}</span>
                        </div>
                    </div>
                    <div class="item-footer">
                        <span class="ending" ng-if="item.bid_end_utc">
                            Ends {{ item.bid_end_utc | date:'medium' }}
                        </span>
                    </div>
                </div>
            </article>
        </section>

        <section class="empty-state" ng-if="!home.items.length">
            <p>No items found. Try adjusting your filters.</p>
        </section>

        <section class="pagination" ng-if="home.pagination.total_pages > 1">
            <button type="button"
                    ng-disabled="home.pagination.page === 1"
                    ng-click="home.load(home.pagination.page - 1)">
                Previous
            </button>
            <span>Page {{ home.pagination.page }} of {{ home.pagination.total_pages }}</span>
            <button type="button"
                    ng-disabled="home.pagination.page === home.pagination.total_pages"
                    ng-click="home.load(home.pagination.page + 1)">
                Next
            </button>
        </section>
    </main>

    <script>
        window.__APP_BOOTSTRAP__ = {
            user: <?php echo json_encode($currentUser); ?>
        };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script src="/frontend/app.js"></script>
    <script src="/frontend/services/api.service.js"></script>
    <script src="/frontend/services/auth.service.js"></script>
    <script src="/frontend/services/saved.service.js"></script>
    <script src="/frontend/controllers/auth.controller.js"></script>
    <script src="/frontend/controllers/home.controller.js"></script>
</body>
</html>
