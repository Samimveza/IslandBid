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
        vm.uploadPreviewUrl = null;
        vm.uploadingImage = false;
        vm.editItemId = (window.__APP_BOOTSTRAP__ && window.__APP_BOOTSTRAP__.edit_item_id) || null;

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

            var payload = angular.copy(vm.form);
            function normalizeDateForServer(d) {
                if (!d) { return null; }
                if (Object.prototype.toString.call(d) === '[object Date]') {
                    if (isNaN(d.getTime())) { return null; }
                    return d.toISOString().slice(0, 19).replace('T', ' ');
                }
                return d;
            }
            payload.bid_start_utc = normalizeDateForServer(payload.bid_start_utc);
            payload.bid_end_utc = normalizeDateForServer(payload.bid_end_utc);

            ApiService.post('/api/items', payload).then(function (response) {
                vm.submitting = false;
                var data = response.data && response.data.data;
                vm.successMessage = vm.createdItemId ? 'Listing updated successfully.' : 'Listing created successfully.';
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

            vm.uploadingImage = true;
            ApiService.post('/api/items/upload-image', formData, { isMultipart: true }).then(function (response) {
                vm.uploadingImage = false;
                var data = response.data && response.data.data;
                if (data && data.image) {
                    vm.images.push(data.image);
                }
                if (vm.uploadPreviewUrl) {
                    URL.revokeObjectURL(vm.uploadPreviewUrl);
                    vm.uploadPreviewUrl = null;
                }
                vm.uploadFile = null;
                vm.uploadDisplayOrder = null;
                vm.uploadIsPrimary = false;
            }).catch(function (error) {
                vm.uploadingImage = false;
                var data = error.data || {};
                vm.uploadError = data.message || 'Upload failed.';
            });
        };

        if (vm.editItemId) {
            ApiService.get('/api/items/edit-data', { id_item: vm.editItemId }).then(function (response) {
                var data = response.data && response.data.data;
                if (!data || !data.item) {
                    return;
                }
                var item = data.item;
                vm.createdItemId = item.id_item;
                vm.form.id_item = item.id_item;
                vm.form.id_category = item.id_category;
                vm.form.listing_type = item.listing_type;
                vm.form.title = item.title;
                vm.form.short_description = item.short_description;
                vm.form.description = item.description;
                vm.form.start_price = item.start_price !== null ? parseFloat(item.start_price) : null;
                vm.form.fixed_price = item.fixed_price !== null ? parseFloat(item.fixed_price) : null;
                vm.form.bid_start_utc = item.bid_start_utc ? new Date(String(item.bid_start_utc).replace(' ', 'T')) : null;
                vm.form.bid_end_utc = item.bid_end_utc ? new Date(String(item.bid_end_utc).replace(' ', 'T')) : null;
                vm.form.location_text = item.location_text;
                vm.form.item_status = item.item_status;
                vm.form.is_published = !!item.is_published;
                vm.form.fields = {};
                vm.images = data.images || [];

                CategoryService.getFields(vm.form.id_category).then(function (fields) {
                    vm.fields = fields;
                    var fromApiFields = data.fields || [];
                    for (var i = 0; i < fromApiFields.length; i += 1) {
                        var f = fromApiFields[i];
                        var value = null;
                        if (f.field_type === 'text') { value = f.field_value_text; }
                        else if (f.field_type === 'number' || f.field_type === 'decimal') { value = f.field_value_number; }
                        else if (f.field_type === 'boolean') { value = f.field_value_boolean; }
                        else if (f.field_type === 'date') { value = f.field_value_date; }
                        else if (f.field_type === 'select') { value = f.field_value_option; }
                        if (value !== null && value !== undefined) {
                            vm.form.fields[f.id_category_field] = value;
                        }
                    }
                });
            });
        }
    });
})();

