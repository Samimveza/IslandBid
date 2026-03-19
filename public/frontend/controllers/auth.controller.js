/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('AuthController', function AuthController(AuthService) {
        var vm = this;
        vm.user = AuthService.getUser();

        vm.logout = function logout() {
            AuthService.logout().then(function () {
                vm.user = AuthService.getUser();
            });
        };
    });
})();
