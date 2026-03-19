/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').service('ApiService', function ApiService($http, $q) {
        function handleResponse(promise) {
            return promise.catch(function (response) {
                if (response && response.status === 401) {
                    // Unauthenticated – send user to login
                    if (window.location.pathname !== '/login') {
                        window.location.href = '/login';
                    }
                }
                return $q.reject(response);
            });
        }

        this.post = function post(url, payload, options) {
            if (options && options.isMultipart) {
                return handleResponse($http.post(url, payload, {
                    transformRequest: angular.identity,
                    headers: { 'Content-Type': undefined }
                }));
            }
            return handleResponse($http.post(url, payload));
        };

        this.get = function get(url, params) {
            return handleResponse($http.get(url, { params: params || {} }));
        };
    });
})();
