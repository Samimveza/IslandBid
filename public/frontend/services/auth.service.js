/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').service('AuthService', function AuthService($q, ApiService) {
        var currentUser = (window.__APP_BOOTSTRAP__ && window.__APP_BOOTSTRAP__.user) || null;

        this.getUser = function getUser() {
            return currentUser;
        };

        this.isAuthenticated = function isAuthenticated() {
            return !!currentUser;
        };

        this.login = function login(credentials) {
            return ApiService.post('/api/auth/login', credentials).then(function (response) {
                currentUser = response.data && response.data.data && response.data.data.user;
                return currentUser;
            });
        };

        this.logout = function logout() {
            return ApiService.post('/api/auth/logout', {}).then(function () {
                currentUser = null;
            });
        };

        this.refresh = function refresh() {
            return ApiService.get('/api/auth/me').then(function (response) {
                currentUser = response.data && response.data.data && response.data.data.user;
                return currentUser;
            }).catch(function () {
                currentUser = null;
                return null;
            });
        };

        this.requireAuth = function requireAuth() {
            var deferred = $q.defer();
            if (currentUser) {
                deferred.resolve(currentUser);
            } else {
                this.refresh().then(function (user) {
                    if (user) {
                        deferred.resolve(user);
                    } else {
                        deferred.reject('AUTH_REQUIRED');
                    }
                });
            }
            return deferred.promise;
        };
    });
})();

