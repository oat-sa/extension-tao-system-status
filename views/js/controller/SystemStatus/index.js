define([
    'tpl!taoSystemStatus/controller/SystemStatus/reportDetailsModal',
    'taoSystemStatus/tasksMonitoring/tasksStatistics'
], function(layout, tasksStatisticsFactory) {
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

        $modal.on('closed.modal', function() {
            $modal.modal('destroy');
            $(this).remove();
        });
    }

    return {
        start : function() {
            var tasksStatistics;

            $container = $('#system-status-report');
            $container.on('click', '.js-feedback-details-button', function () {
                var $modalContent = $(this).closest('.js-report').find('.js-feedback-details');
                showTaskReport($modalContent);
            });

            tasksStatistics = tasksStatisticsFactory();
            $container.on('click', '.taskqueue_log_report-statistics-button', function () {
                $('.js-tasks-statistics-modal').modal().on('opened.modal', function () {
                         tasksStatistics.render();
                     });
            });

        }
    }
});
