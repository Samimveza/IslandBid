/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').service('SavedService', function SavedService($q, ApiService, AuthService) {
        var savedIds = null;

        function ensureLoaded() {
            var deferred = $q.defer();
            if (savedIds !== null) {
                deferred.resolve(savedIds);
                return deferred.promise;
            }
            if (!AuthService.isAuthenticated()) {
                savedIds = [];
                deferred.resolve(savedIds);
                return deferred.promise;
            }
            ApiService.get('/api/saved-items').then(function (response) {
                var data = response.data && response.data.data;
                savedIds = data.item_ids || [];
                deferred.resolve(savedIds);
            }).catch(function () {
                savedIds = [];
                deferred.resolve(savedIds);
            });
            return deferred.promise;
        }

        this.isSaved = function isSaved(idItem) {
            if (!savedIds) {
                return false;
            }
            var key = String(idItem);
            for (var i = 0; i < savedIds.length; i += 1) {
                if (String(savedIds[i]) === key) {
                    return true;
                }
            }
            return false;
        };

        this.toggle = function toggle(idItem) {
            return ensureLoaded().then(function () {
                if (!AuthService.isAuthenticated()) {
                    return $q.reject('AUTH_REQUIRED');
                }
                var isSavedNow = savedIds.indexOf(idItem) !== -1;
                var url = isSavedNow ? '/api/saved-items/unsave' : '/api/saved-items/save';
                return ApiService.post(url, { id_item: idItem }).then(function () {
                    if (isSavedNow) {
                        savedIds = savedIds.filter(function (id) { return id !== idItem; });
                    } else {
                        savedIds.push(idItem);
                    }
                    return !isSavedNow;
                });
            });
        };

        this.ensureLoaded = ensureLoaded;
    });
})();

