/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').controller('ItemController', function ItemController(AuthService, ApiService, SavedService) {
        var vm = this;
        vm.user = AuthService.getUser();
        vm.item = (window.__APP_BOOTSTRAP__ && window.__APP_BOOTSTRAP__.item) || {};
        vm.bidAmount = null;
        vm.bidError = '';
        vm.bidSuccess = '';
        vm.bidSubmitting = false;
        vm.currentHighestBid = vm.item.current_highest_bid || vm.item.start_price || 0;
        vm.bids = [];
        vm.myActiveBid = null;
        vm.isSaved = false;
        vm.canBid = !!vm.user &&
            vm.item &&
            vm.item.id_user !== vm.user.id_user &&
            (vm.item.listing_type === 'bid' || vm.item.listing_type === 'both');

        vm.logout = function logout() {
            AuthService.logout().then(function () {
                vm.user = AuthService.getUser();
            });
        };

        vm.placeBid = function placeBid() {
            vm.bidError = '';
            vm.bidSuccess = '';
            if (!vm.bidAmount || vm.bidAmount <= 0) {
                vm.bidError = 'Enter a valid bid amount.';
                return;
            }
            vm.bidSubmitting = true;
            ApiService.post('/api/bids/place', {
                id_item: vm.item.id_item,
                bid_amount: vm.bidAmount
            }).then(function (response) {
                vm.bidSubmitting = false;
                var bid = response.data && response.data.data && response.data.data.bid;
                if (bid) {
                    vm.currentHighestBid = bid.current_highest_bid;
                    vm.bidSuccess = 'Bid placed successfully.';
                    vm.bidAmount = null;
                    vm.loadBids();
                }
            }).catch(function (error) {
                vm.bidSubmitting = false;
                var data = error.data || {};
                vm.bidError = data.message || 'Unable to place bid.';
            });
        };

        vm.updateBid = function updateBid() {
            vm.bidError = '';
            vm.bidSuccess = '';
            if (!vm.bidAmount || vm.bidAmount <= 0) {
                vm.bidError = 'Enter a valid bid amount.';
                return;
            }
            vm.bidSubmitting = true;
            ApiService.post('/api/bids/update', {
                id_item: vm.item.id_item,
                bid_amount: vm.bidAmount
            }).then(function (response) {
                vm.bidSubmitting = false;
                var bid = response.data && response.data.data && response.data.data.bid;
                if (bid) {
                    vm.currentHighestBid = bid.current_highest_bid;
                    vm.bidSuccess = 'Bid updated successfully.';
                    vm.bidAmount = null;
                    vm.loadBids();
                }
            }).catch(function (error) {
                vm.bidSubmitting = false;
                var data = error.data || {};
                vm.bidError = data.message || 'Unable to update bid.';
            });
        };

        vm.removeBid = function removeBid() {
            vm.bidError = '';
            vm.bidSuccess = '';
            if (!vm.myActiveBid) {
                vm.bidError = 'No active bid to remove.';
                return;
            }
            if (!window.confirm('Are you sure you want to remove your bid?')) {
                return;
            }
            vm.bidSubmitting = true;
            ApiService.post('/api/bids/remove', {
                id_item: vm.item.id_item
            }).then(function (response) {
                vm.bidSubmitting = false;
                var bid = response.data && response.data.data && response.data.data.bid;
                vm.currentHighestBid = bid && bid.current_highest_bid ? bid.current_highest_bid : (vm.item.start_price || 0);
                vm.bidSuccess = 'Bid removed successfully.';
                vm.bidAmount = null;
                vm.loadBids();
            }).catch(function (error) {
                vm.bidSubmitting = false;
                var data = error.data || {};
                vm.bidError = data.message || 'Unable to remove bid.';
            });
        };

        vm.loadBids = function loadBids() {
            ApiService.get('/api/bids/by-item', { id_item: vm.item.id_item }).then(function (response) {
                var data = response.data && response.data.data;
                vm.bids = data.bids || [];
                vm.myActiveBid = null;
                if (vm.user && vm.bids.length) {
                    for (var i = 0; i < vm.bids.length; i += 1) {
                        if (vm.bids[i].id_user === vm.user.id_user && vm.bids[i].bid_status === 'active') {
                            vm.myActiveBid = vm.bids[i];
                            break;
                        }
                    }
                }
            });
        };

        vm.toggleSaved = function toggleSaved() {
            if (!AuthService.isAuthenticated()) {
                window.location.href = '/login';
                return;
            }
            SavedService.toggle(vm.item.id_item).then(function (saved) {
                vm.isSaved = saved;
            });
        };

        SavedService.ensureLoaded().then(function () {
            vm.isSaved = SavedService.isSaved(vm.item.id_item);
        });

        vm.loadBids();
    });
})();

