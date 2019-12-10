define([
    'tpl!taoSystemStatus/controller/SystemStatus/reportDetailsModal'
], function(layout) {
    'use strict';

    var $container;

    function showTaskReport($modalContent) {
        var $modal = $(layout({data: $modalContent.html()}));
        $container.append($modal);
        $modal.modal({
            startClosed : true,
            minWidth : 450
        });
        $modal.modal('open');
    }

    return {
        start : function() {
            $container = $('#system-status-report');
            $container.on('click', '.js-feedback-details-button', function () {
                var $modalContent = $(this).closest('.js-report').find('.js-feedback-details');
                showTaskReport($modalContent);
            });
        }
    }
});
