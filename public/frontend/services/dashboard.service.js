/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').service('DashboardService', function DashboardService(ApiService) {
        this.getSellerListings = function getSellerListings(status) {
            return ApiService.get('/api/dashboards/seller', { status: status || 'all' }).then(function (response) {
                var data = response.data && response.data.data;
                return data || { items: [], segmented: {} };
            });
        };

        this.getBuyerDashboard = function getBuyerDashboard() {
            return ApiService.get('/api/dashboards/buyer').then(function (response) {
                var data = response.data && response.data.data;
                return data || { active_bids: [], won_items: [], lost_items: [], saved_items: [] };
            });
        };
    });
})();

