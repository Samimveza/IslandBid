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
    <title>Create Listing - IslandBid</title>
    <meta name="description" content="Create a new bidding or fixed-price listing on IslandBid.">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body class="create-page" ng-controller="CreateItemController as vm">
    <header class="topbar">
        <div class="container">
            <h1>IslandBid</h1>
            <div class="auth-strip">
                <span>Logged in as <?php echo htmlspecialchars($currentUser['first_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <button type="button" ng-click="vm.logout()">Logout</button>
            </div>
        </div>
    </header>

    <main class="container create-layout">
        <div class="create-card">
            <h2>Create a new listing</h2>

            <form novalidate ng-submit="vm.submit()">
                <div class="form-row">
                    <div class="form-field">
                        <label>Category</label>
                        <select ng-model="vm.form.id_category"
                                ng-options="c.id_category as c.category_name for c in vm.categories"
                                ng-change="vm.onCategoryChange()"
                                required>
                            <option value="">Select category...</option>
                        </select>
                        <div class="error-text" ng-if="vm.errors.id_category">{{ vm.errors.id_category }}</div>
                    </div>

                    <div class="form-field">
                        <label>Listing type</label>
                        <select ng-model="vm.form.listing_type" required>
                            <option value="bid">Bid</option>
                            <option value="fixed_price">Fixed price</option>
                            <option value="both">Bid &amp; Buy now</option>
                        </select>
                        <div class="error-text" ng-if="vm.errors.listing_type">{{ vm.errors.listing_type }}</div>
                    </div>
                </div>

                <div class="form-field">
                    <label>Title</label>
                    <input type="text" ng-model="vm.form.title" required>
                    <div class="error-text" ng-if="vm.errors.title">{{ vm.errors.title }}</div>
                </div>

                <div class="form-field">
                    <label>Short description</label>
                    <input type="text" ng-model="vm.form.short_description">
                </div>

                <div class="form-field">
                    <label>Full description</label>
                    <textarea ng-model="vm.form.description" rows="5" required></textarea>
                    <div class="error-text" ng-if="vm.errors.description">{{ vm.errors.description }}</div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>Start price (MUR)</label>
                        <input type="number" min="0" step="1" ng-model="vm.form.start_price">
                    </div>
                    <div class="form-field">
                        <label>Fixed price (MUR)</label>
                        <input type="number" min="0" step="1" ng-model="vm.form.fixed_price">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>Bid start (UTC)</label>
                        <input type="datetime-local" ng-model="vm.form.bid_start_utc">
                    </div>
                    <div class="form-field">
                        <label>Bid end (UTC)</label>
                        <input type="datetime-local" ng-model="vm.form.bid_end_utc">
                    </div>
                </div>

                <div class="form-field">
                    <label>Location</label>
                    <input type="text" ng-model="vm.form.location_text">
                </div>

                <div class="dynamic-fields" ng-if="vm.fields.length">
                    <h3>Category details</h3>
                    <div class="form-field" ng-repeat="field in vm.fields track by field.id_category_field">
                        <label>{{ field.field_label }} <span ng-if="field.is_required">*</span></label>

                        <input ng-if="field.field_type === 'text'" type="text"
                               ng-model="vm.form.fields[field.id_category_field]">

                        <input ng-if="field.field_type === 'number' || field.field_type === 'decimal'"
                               type="number" ng-model="vm.form.fields[field.id_category_field]">

                        <input ng-if="field.field_type === 'date'" type="date"
                               ng-model="vm.form.fields[field.id_category_field]">

                        <select ng-if="field.field_type === 'select'"
                                ng-model="vm.form.fields[field.id_category_field]"
                                ng-options="o.option_value as o.option_label for o in field.options">
                            <option value="">Select...</option>
                        </select>

                        <input ng-if="field.field_type === 'boolean'" type="checkbox"
                               ng-model="vm.form.fields[field.id_category_field]">

                        <div class="error-text" ng-if="vm.fieldErrors[field.id_category_field]">
                            {{ vm.fieldErrors[field.id_category_field] }}
                        </div>
                    </div>
                </div>

                <div class="form-row status-row">
                    <div class="form-field">
                        <label>Status</label>
                        <select ng-model="vm.form.item_status">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>
                            <input type="checkbox" ng-model="vm.form.is_published">
                            Publish immediately
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary" ng-disabled="vm.submitting">
                    {{ vm.submitting ? 'Saving...' : 'Save listing' }}
                </button>

                <div class="alert-success" ng-if="vm.successMessage">
                    {{ vm.successMessage }}
                </div>
                <div class="alert-error" ng-if="vm.errorMessage">
                    {{ vm.errorMessage }}
                </div>
            </form>

            <section class="upload-section" ng-if="vm.createdItemId">
                <h3>Images</h3>
                <div class="upload-row">
                    <input type="file" file-input="vm.uploadFile">
                    <input type="number" min="0" placeholder="Display order" ng-model="vm.uploadDisplayOrder">
                    <label>
                        <input type="checkbox" ng-model="vm.uploadIsPrimary"> Primary
                    </label>
                    <button type="button" class="btn-filter" ng-click="vm.uploadImage()">Upload</button>
                </div>
                <div class="upload-error" ng-if="vm.uploadError">
                    {{ vm.uploadError }}
                </div>
                <div class="image-list" ng-if="vm.images.length">
                    <div class="image-thumb" ng-repeat="img in vm.images track by img.id_item_document">
                        <img ng-src="{{ img.full_server_url }}" alt="{{ img.file_name }}">
                        <div class="meta">
                            <span class="name">{{ img.file_name }}</span>
                            <span class="badge" ng-if="img.is_primary">Primary</span>
                            <span class="order" ng-if="img.display_order !== null">#{{ img.display_order }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        window.__APP_BOOTSTRAP__ = { user: <?php echo json_encode($currentUser); ?> };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script src="/frontend/app.js"></script>
    <script src="/frontend/services/api.service.js"></script>
    <script src="/frontend/services/auth.service.js"></script>
    <script src="/frontend/services/category.service.js"></script>
    <script src="/frontend/directives/file-input.directive.js"></script>
    <script src="/frontend/controllers/create-item.controller.js"></script>
</body>
</html>

