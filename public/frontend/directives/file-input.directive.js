/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').directive('fileInput', function () {
        return {
            restrict: 'A',
            scope: {
                fileInput: '='
            },
            link: function (scope, element) {
                element.on('change', function (event) {
                    var files = event.target.files;
                    if (files && files.length) {
                        scope.$apply(function () {
                            scope.fileInput = files[0];
                        });
                    }
                });
            }
        };
    });
})();

