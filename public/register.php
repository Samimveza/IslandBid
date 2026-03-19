<?php

require_once __DIR__ . '/../src/helpers/SessionAuth.php';
SessionAuth::start();
?>
<!doctype html>
<html lang="en" ng-app="islandBidApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - IslandBid</title>
    <meta name="description" content="Create your IslandBid account to start buying and selling.">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body class="auth-page" ng-controller="RegisterController as reg">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Create your account</h1>
            <p class="auth-subtitle">Join IslandBid to bid or buy at fixed prices.</p>

            <form novalidate ng-submit="reg.submit()">
                <div class="form-field">
                    <label>First name</label>
                    <input type="text" ng-model="reg.form.first_name" required>
                    <div class="error-text" ng-if="reg.errors.first_name">{{ reg.errors.first_name }}</div>
                </div>

                <div class="form-field">
                    <label>Last name</label>
                    <input type="text" ng-model="reg.form.last_name" required>
                    <div class="error-text" ng-if="reg.errors.last_name">{{ reg.errors.last_name }}</div>
                </div>

                <div class="form-field">
                    <label>Email</label>
                    <input type="email" ng-model="reg.form.email" required>
                    <div class="error-text" ng-if="reg.errors.email">{{ reg.errors.email }}</div>
                </div>

                <div class="form-field">
                    <label>Phone (optional)</label>
                    <input type="tel" ng-model="reg.form.phone">
                    <div class="error-text" ng-if="reg.errors.phone">{{ reg.errors.phone }}</div>
                </div>

                <div class="form-field">
                    <label>Password</label>
                    <input type="password" ng-model="reg.form.password" required minlength="8">
                    <div class="error-text" ng-if="reg.errors.password">{{ reg.errors.password }}</div>
                </div>

                <button type="submit" class="btn-primary" ng-disabled="reg.submitting">
                    {{ reg.submitting ? 'Creating account...' : 'Create account' }}
                </button>

                <div class="alert-success" ng-if="reg.message && !Object.keys(reg.errors).length">
                    {{ reg.message }}
                </div>
                <div class="alert-error" ng-if="Object.keys(reg.errors).length">
                    {{ reg.message || 'Please fix the errors above.' }}
                </div>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script src="/frontend/app.js"></script>
    <script src="/frontend/services/api.service.js"></script>
    <script src="/frontend/controllers/register.controller.js"></script>
</body>
</html>

