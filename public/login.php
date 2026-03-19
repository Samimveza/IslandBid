<?php

require_once __DIR__ . '/../src/helpers/SessionAuth.php';
SessionAuth::start();
if (SessionAuth::user()) {
    header('Location: /');
    exit;
}
?>
<!doctype html>
<html lang="en" ng-app="islandBidApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IslandBid</title>
    <meta name="description" content="Login to your IslandBid account.">
    <link rel="stylesheet" href="/frontend/styles/main.css">
</head>
<body class="auth-page" ng-controller="LoginController as login">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Welcome back</h1>
            <p class="auth-subtitle">Sign in to manage your bids and listings.</p>

            <form novalidate ng-submit="login.submit()">
                <div class="form-field">
                    <label>Email</label>
                    <input type="email" ng-model="login.form.email" required>
                </div>

                <div class="form-field">
                    <label>Password</label>
                    <input type="password" ng-model="login.form.password" required>
                </div>

                <button type="submit" class="btn-primary" ng-disabled="login.submitting">
                    {{ login.submitting ? 'Signing in...' : 'Sign in' }}
                </button>

                <div class="alert-error" ng-if="login.error">
                    {{ login.error }}
                </div>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script src="/frontend/app.js"></script>
    <script src="/frontend/services/api.service.js"></script>
    <script src="/frontend/services/auth.service.js"></script>
    <script src="/frontend/controllers/login.controller.js"></script>
</body>
</html>

