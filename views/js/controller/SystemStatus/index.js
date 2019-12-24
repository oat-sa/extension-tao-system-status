define([
    'jquery',
    'i18n',
    'taoSystemStatus/dataProvider/reportsDataProvider',
    'tpl!taoSystemStatus/controller/SystemStatus/tpl/reportTable',
    'tpl!taoSystemStatus/controller/SystemStatus/tpl/chartsContainer',
    'tpl!taoSystemStatus/controller/SystemStatus/tpl/taskQueue',
    'taoSystemStatus/tasksMonitoring/tasksStatistics',
    'c3'
], function ($, __, reportsDataProvider, reportTableTpl, chartsContainerTpl, taskQueueTpl, tasksStatistics, c3) {
    'use strict';

    const reportCategories = {
        configuration: 'TAO Configuration',
        configurationValues: 'Configuration Values',
        healthReadinessCheck: 'Health/Readiness check',
        monitoringStatistics: 'Monitoring / Statistics',
    }

    return {
        start: function () {
            const $container = $('#system-status-report');
            const $configurationTablesContainer = $('#system-status-configuration-tables');
            const {
                configuration,
                configurationValues,
                healthReadinessCheck,
                monitoringStatistics,
            } = reportCategories;

            const renderMonitoringStatistics = {
                'oat\\taoSystemStatus\\model\\Check\\System\\TaskQueueFailsCheck': ({ children }) => {
                    $container.append($(reportTableTpl({
                        category: configuration,
                        columns: [__('Task'), __('Date'), ''],
                        data: children
                            .map(({ message }) => ({
                                rows: [message, new Date().toLocaleString()],
                                detailsButton: true,
                            }))
                    })));
                },
                'oat\\taoSystemStatus\\model\\Check\\System\\TaskQueueFinishedCheck': ({ data: { P1D, P1W, P1M } }) => {
                    const $graphContainer = $('<div></div>');
                    $container.append($graphContainer);

                    tasksStatistics({
                        defaultInterval: 'P1D',
                        intervals: [
                            { label: __('Last Day'), value: 'P1D' },
                            { label: __('Last Week'), value: 'P1W' },
                            { label: __('Last Month'), value: 'P1M' }
                        ],
                        data: { P1D, P1W, P1M },
                    })
                        .render($graphContainer);
                }
            };

            reportsDataProvider.getReports().then(({ report: { children: reports } }) => {
                // Group reports by category
                const reportGroups = reports.reduce(
                    (agg, item) => {
                        const { data: { category } } = item;

                        agg[category] && agg[category].push(item);

                        return agg;
                    },
                    {
                        [configuration]: [],
                        [configurationValues]: [],
                        [healthReadinessCheck]: [],
                        [monitoringStatistics]: [],
                    }
                );

                const $configurationTable = $(reportTableTpl({
                    category: configuration,
                    columns: [__('Status'), __('Description'), __('Date')],
                    data: reportGroups[configuration]
                        .map(({ type, data: { details, date } }) => ({
                            type,
                            [`is${type}`]: true,
                            rows: [details, new Date(date * 1000).toLocaleString()],
                        }))
                }));

                const $configurationValuesTable = $(reportTableTpl({
                    category: configurationValues,
                    columns: [__('Status'), __('Description'), __('Date')],
                    data: reportGroups[configurationValues]
                        .map(({ type, message, data: { details } }) => ({
                            type,
                            [`is${type}`]: true,
                            rows: [details, message],
                        }))
                }));

                const $healthReadinessStatusTable = $(reportTableTpl({
                    category: healthReadinessCheck,
                    columns: [__('Status'), __('Description'), __('Date')],
                    data: reportGroups[healthReadinessCheck]
                        .map(({ type, message, data: { details } }) => ({
                            type,
                            [`is${type}`]: true,
                            rows: [details, message],
                        }))
                }));

                $configurationTablesContainer.append($configurationTable);
                $configurationTablesContainer.append($configurationValuesTable);
                $container.append($healthReadinessStatusTable);

                reportGroups[monitoringStatistics]
                    .forEach((check) => {
                        const { data: { check_id } } = check;

                        renderMonitoringStatistics[check_id] && renderMonitoringStatistics[check_id](check);
                    });

                const $chartContainer = $(chartsContainerTpl());
                $container.append($chartContainer);

                c3.generate({
                    bindto: '.js-tasks-donut-2',
                    data: {
                        columns: [
                            ['data1', 30],
                            ['data2', 120],
                        ],
                        type: 'donut',
                    },
                    donut: {
                        title: 'Iris Petal Width',
                    },
                    width: 500,
                });

                c3.generate({
                    bindto: '.js-tasks-donut-1',
                    data: {
                        columns: [
                            ['data1', 30],
                            ['data2', 120],
                        ],
                        type: 'donut',
                    },
                    donut: {
                        title: 'Iris Petal Width'
                    }
                });

                const $taskQueue = $(taskQueueTpl());
                $chartContainer.append($taskQueue);
            })
        }
    }
});
