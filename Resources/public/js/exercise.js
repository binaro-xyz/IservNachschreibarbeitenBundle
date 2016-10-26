/**
 * Exercise module
 */
(function(IServ, $) {
    // Create ng module and register it in the core app.
    var module = angular.module('IServ.Exercise', ['IServ.Editable', 'IServ.Message', 'IServ.Form']);
    IServ.registerModule('IServ.Exercise');

    if (typeof $.fn.editable !== 'undefined') {
        // Configure X-editable
        module.config(['editableConfigProvider', function(editableConfigProvider) {
            editableConfigProvider.options = {
                mode: 'inline',
                success: function(response, newValue) {
                    if (response.status === 'error') {
                        return response.message; // Message will be shown in editable form
                    }
                },
                error: function(response, newValue) {
                    if (typeof response.message !== 'undefined') {
                        return response.message;
                    }
                    else {
                        return 'There was an error with your request!';
                    }
                }
            };
        }]);
    }

    module.controller('ExerciseAttachmentController', function($scope) {
        // noop?
    });

}(IServ, jQuery));
