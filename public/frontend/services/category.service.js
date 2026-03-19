/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').service('CategoryService', function CategoryService(ApiService) {
        this.getCategories = function getCategories() {
            return ApiService.get('/api/categories').then(function (response) {
                var data = response.data && response.data.data;
                return data.categories || [];
            });
        };

        this.getFields = function getFields(idCategory) {
            return ApiService.get('/api/categories/fields', { id_category: idCategory }).then(function (response) {
                var data = response.data && response.data.data;
                return data.fields || [];
            });
        };
    });
})();

