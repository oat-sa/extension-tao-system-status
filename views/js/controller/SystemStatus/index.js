/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define([
    'jquery',
    'i18n',
    'taoSystemStatus/dataProvider/reportsDataProvider',
    'tpl!taoSystemStatus/controller/SystemStatus/tpl/reportTable',
    'tpl!taoSystemStatus/controller/SystemStatus/tpl/chartsContainer',
    'tpl!taoSystemStatus/controller/SystemStatus/tpl/taskQueue',
    'taoSystemStatus/tasksMonitoring/tasksStatistics',
    'layout/loading-bar',
    'ui/feedback',
    'c3',
], function (
    $,
    __,
    reportsDataProvider,
    reportTableTpl,
    chartsContainerTpl,
    taskQueueTpl,
    tasksStatistics,
    loadingBar,
    feedback,
    c3
) {
        'use strict';

        return {
            start: function () {
                const $container = $('#system-status-report');
                const $configurationTablesContainer = $('#system-status-configuration-tables');

                loadingBar.start();

                reportsDataProvider.getReports()
                    .then((reports) => {
                        const {
                            configuration,
                            configurationValues,
                            healthCheck,
                            taskQueueFails,
                            taskQueueFinished,
                            taskQueueMonitoring,
                            redisFreeSpace,
                            rdsFreeSpace,
                        } = reports;

                        if (configuration.length) {
                            $configurationTablesContainer.append($(reportTableTpl({
                                category: __('TAO Configuration'),
                                columns: [__('Status'), __('Description'), __('Date')],
                                data: configuration
                                    .map(({ type, data: { details, date } }) => ({
                                        type,
                                        [`is${type}`]: true,
                                        rows: [details, new Date(date * 1000).toLocaleString()],
                                    }))
                            })));
                        }

                        if (configurationValues.length) {
                            $configurationTablesContainer.append($(reportTableTpl({
                                category: __('Configuration Values'),
                                columns: [__('Status'), __('Description'), __('Value')],
                                data: configurationValues
                                    .map(({ type, message, data: { details } }) => ({
                                        type,
                                        [`is${type}`]: true,
                                        rows: [details, message.replace(/\r?\n/g, '<br />')],
                                    }))
                            })));
                        }

                        if (healthCheck.length) {
                            $container.append($(reportTableTpl({
                                category: __('Health/Readiness check'),
                                columns: [__('Status'), __('Description'), __('Details')],
                                data: healthCheck
                                    .map(({ type, message, data: { details } }) => ({
                                        type,
                                        [`is${type}`]: true,
                                        rows: [details, message.replace(/\r?\n/g, '<br />')],
                                    }))
                            })));
                        }

                        if (taskQueueFails[0]) {
                            const $table = $(reportTableTpl({
                                category: __('Last failed tasks in the task queue'),
                                columns: [__('Task'), __('Date'), ''],
                                data: taskQueueFails[0].children
                                    .map(({ children, data: { task_label, task_report_time } }) => ({
                                        reportData: JSON.stringify(children),
                                        detailsButton: true,
                                        rows: [task_label, task_report_time],
                                    })),
                            }));

                            $container.append($table);

                            $('.system-status-table__details-button', $table)
                                .on('click', ({ target }) => {
                                    console.log($(target).data('report'));
                                });
                        }

                        if (taskQueueFinished[0]) {
                            const { data: { P1D, P1W, P1M } } = taskQueueFinished[0];
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

                        const $chartContainer = $(chartsContainerTpl());
                        $container.append($chartContainer);

                        if (redisFreeSpace[0]) {
                            c3.generate({
                                bindto: '.js-tasks-donut-2',
                                data: {
                                    columns: [
                                        [__('Free space'), redisFreeSpace[0].data.value],
                                        [__('Used space'), 100 - redisFreeSpace[0].data.value],
                                    ],
                                    type: 'donut',
                                },
                                width: 500,
                            });
                        }

                        if (rdsFreeSpace[0]) {
                            c3.generate({
                                bindto: '.js-tasks-donut-1',
                                data: {
                                    columns: [
                                        [__('Free space'), rdsFreeSpace[0].data.value],
                                        [__('Used space'), 100 - rdsFreeSpace[0].data.value],
                                    ],
                                    type: 'donut',
                                },
                            });
                        }

                        if (taskQueueMonitoring[0]) {
                            const $taskQueue = $(taskQueueTpl({ taskCount: taskQueueMonitoring[0].data.report_value }));
                            $chartContainer.append($taskQueue);
                        }
                    })
                    //.catch(() => feedback().error(__('Something went wrong.')))
                    .finally(() => loadingBar.stop());
            }
        }
    });
