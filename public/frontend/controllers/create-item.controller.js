/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('CreateItemController', function CreateItemController(ApiService, AuthService, CategoryService) {
        var vm = this;
        vm.form = {
            item_status: 'draft',
            listing_type: 'bid',
            fields: {}
        };
        vm.errors = {};
        vm.fieldErrors = {};
        vm.successMessage = '';
        vm.errorMessage = '';
        vm.submitting = false;
        vm.categories = [];
        vm.fields = [];
        vm.createdItemId = null;
        vm.images = [];
        vm.uploadFile = null;
        vm.uploadDisplayOrder = null;
        vm.uploadIsPrimary = false;

        vm.logout = function logout() {
            AuthService.logout().then(function () {
                window.location.href = '/';
            });
        };

        CategoryService.getCategories().then(function (categories) {
            vm.categories = categories;
        });

        vm.onCategoryChange = function onCategoryChange() {
            vm.fields = [];
            vm.form.fields = {};
            vm.fieldErrors = {};
            if (!vm.form.id_category) {
                return;
            }
            CategoryService.getFields(vm.form.id_category).then(function (fields) {
                vm.fields = fields;
            });
        };

        vm.submit = function submit() {
            vm.submitting = true;
            vm.errors = {};
            vm.fieldErrors = {};
            vm.successMessage = '';
            vm.errorMessage = '';

            if (vm.createdItemId) {
                vm.form.id_item = vm.createdItemId;
            }

            ApiService.post('/api/items', vm.form).then(function (response) {
                vm.submitting = false;
                var data = response.data && response.data.data;
                vm.successMessage = 'Listing created successfully.';
                if (data && data.item && data.item.seo_slug) {
                    vm.successMessage += ' View it at /item/' + data.item.seo_slug;
                    vm.createdItemId = data.item.id_item;
                }
            }).catch(function (error) {
                vm.submitting = false;
                var data = error.data || {};
                vm.errorMessage = data.message || 'Failed to create listing.';
                vm.errors = data.errors || {};
                if (vm.errors.fields) {
                    vm.fieldErrors = vm.errors.fields;
                }
            });
        };

        vm.uploadImage = function uploadImage() {
            vm.uploadError = '';
            if (!vm.createdItemId || !vm.uploadFile) {
                vm.uploadError = 'Please save the listing and choose a file first.';
                return;
            }

            var formData = new FormData();
            formData.append('id_item', vm.createdItemId);
            formData.append('file', vm.uploadFile);
            if (vm.uploadDisplayOrder !== null && vm.uploadDisplayOrder !== undefined) {
                formData.append('display_order', vm.uploadDisplayOrder);
            }
            if (vm.uploadIsPrimary) {
                formData.append('is_primary', '1');
            }

            ApiService.post('/api/items/upload-image', formData, { isMultipart: true }).then(function (response) {
                var data = response.data && response.data.data;
                if (data && data.image) {
                    vm.images.push(data.image);
                }
                vm.uploadFile = null;
                vm.uploadDisplayOrder = null;
                vm.uploadIsPrimary = false;
            }).catch(function (error) {
                var data = error.data || {};
                vm.uploadError = data.message || 'Upload failed.';
            });
        };
    });
})();

