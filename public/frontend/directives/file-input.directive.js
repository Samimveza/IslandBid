/* global angular */
(function () {
    'use strict';

    angular.module('islandBidApp').directive('fileInput', function () {
        return {
            restrict: 'A',
            scope: {
                fileInput: '=',
                previewModel: '=?'
            },
            link: function (scope, element) {
                element.on('change', function (event) {
                    var files = event.target.files;
                    if (files && files.length) {
                        var file = files[0];
                        scope.$apply(function () {
                            scope.fileInput = file;
                            if (scope.previewModel !== undefined) {
                                if (scope.previewModel) {
                                    URL.revokeObjectURL(scope.previewModel);
                                }
                                scope.previewModel = URL.createObjectURL(file);
                            }
                        });
                    }
                });
            }
        };
    });
})();

