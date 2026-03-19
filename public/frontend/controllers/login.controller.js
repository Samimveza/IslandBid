/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('LoginController', function LoginController(AuthService) {
        var vm = this;
        vm.form = {};
        vm.error = '';
        vm.submitting = false;

        vm.submit = function submit() {
            vm.error = '';
            vm.submitting = true;
            AuthService.login(vm.form).then(function () {
                vm.submitting = false;
                window.location.href = '/';
            }).catch(function (error) {
                vm.submitting = false;
                var data = error.data || {};
                vm.error = data.message || 'Login failed.';
            });
        };
    });
})();

