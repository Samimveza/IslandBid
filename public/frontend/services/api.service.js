/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').service('ApiService', function ApiService($http) {
        this.post = function post(url, payload) {
            return $http.post(url, payload);
        };

        this.get = function get(url, params) {
            return $http.get(url, { params: params || {} });
        };
    });
})();
