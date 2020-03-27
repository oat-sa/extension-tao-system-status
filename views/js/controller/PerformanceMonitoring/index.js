define([
    'tpl!taoSystemStatus/controller/SystemStatus/reportDetailsModal',
    'taoSystemStatus/widgets/executionStatistics'
], function(layout, executionStatisticsFactory) {
    'use strict';

    return {
        start : function() {
            var executionStatistics;
            executionStatistics = executionStatisticsFactory();
            executionStatistics.render();
        }
    }
});
