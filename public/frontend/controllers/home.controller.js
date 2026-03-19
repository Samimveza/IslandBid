/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('HomeController', function HomeController(ApiService, AuthService) {
        var vm = this;
        vm.filters = {
            q: '',
            category_id: '',
            listing_type: '',
            sort: 'newest',
            page: 1
        };
        vm.items = [];
        vm.pagination = {
            page: 1,
            page_size: 12,
            total: 0,
            total_pages: 1
        };

        vm.isAuthenticated = function () {
            return AuthService.isAuthenticated();
        };

        vm.load = function load(page) {
            if (page) {
                vm.filters.page = page;
            }
            ApiService.get('/api/items', vm.filters).then(function (response) {
                var data = response.data && response.data.data;
                vm.items = data.items || [];
                vm.pagination = data.pagination || vm.pagination;
            });
        };

        vm.applySort = function applySort(sort) {
            vm.filters.sort = sort;
            vm.load(1);
        };

        vm.applyListingType = function applyListingType(type) {
            vm.filters.listing_type = type || '';
            vm.load(1);
        };

        vm.search = function search() {
            vm.load(1);
        };

        vm.load(1);
    });
})();

