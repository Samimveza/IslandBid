/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('SellerDashboardController', function SellerDashboardController(DashboardService, AuthService) {
        var vm = this;
        vm.status = 'all';
        vm.items = [];

        vm.load = function load() {
            DashboardService.getSellerListings(vm.status).then(function (data) {
                vm.items = data.items || [];
            });
        };

        vm.setStatus = function setStatus(status) {
            vm.status = status;
            vm.load();
        };

        AuthService.requireAuth().then(function () {
            vm.load();
        }).catch(function () {
            window.location.href = '/login';
        });
    });
})();

