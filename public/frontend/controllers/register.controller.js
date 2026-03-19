/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('RegisterController', function RegisterController(ApiService) {
        var vm = this;
        vm.form = {};
        vm.errors = {};
        vm.message = '';
        vm.submitting = false;

        vm.submit = function submit() {
            vm.errors = {};
            vm.message = '';
            vm.submitting = true;

            ApiService.post('/api/auth/register', vm.form).then(function (response) {
                vm.submitting = false;
                vm.form = {};
                vm.errors = {};
                vm.message = (response.data && response.data.data && response.data.data.message) ||
                    'Registration successful. You can now log in.';
            }).catch(function (error) {
                vm.submitting = false;
                var data = error.data || {};
                vm.message = data.message || 'Registration failed.';
                vm.errors = data.errors || {};
            });
        };
    });
})();

