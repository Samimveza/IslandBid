/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('ItemController', function ItemController(AuthService) {
        var vm = this;
        vm.user = AuthService.getUser();

        vm.logout = function logout() {
            AuthService.logout().then(function () {
                vm.user = AuthService.getUser();
            });
        };

        vm.openBidModal = function openBidModal() {
            // Placeholder for future bid interactions.
            alert('Bidding endpoint to be implemented.');
        };
    });
})();

