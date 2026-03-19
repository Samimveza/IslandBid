/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('BuyerDashboardController', function BuyerDashboardController(DashboardService) {
        var vm = this;
        vm.active_bids = [];
        vm.won_items = [];
        vm.lost_items = [];
        vm.saved_items = [];

        vm.load = function load() {
            DashboardService.getBuyerDashboard().then(function (data) {
                vm.active_bids = data.active_bids || [];
                vm.won_items = data.won_items || [];
                vm.lost_items = data.lost_items || [];
                vm.saved_items = data.saved_items || [];
            });
        };

        vm.load();
    });
})();

