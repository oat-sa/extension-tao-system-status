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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */

define([
    'i18n',
    'taoSystemStatus/checkRenderers/tableRenderer/tableRenderer',
    'taoSystemStatus/checkRenderers/timelineStatisticGraphRenderer/timelineStatisticGraphRenderer',
    'taoSystemStatus/checkRenderers/donutChartRenderer/donutChartRenderer',
    'tpl!taoSystemStatus/checkRenderers/amountRenderer/amountRenderer',
], function (
    __,
    tableRenderer,
    timelineStatisticGraphRenderer,
    donutChartRenderer,
    amountRendererTpl
) {
        const $container = $('#system-status-report');
        const $chartsContainer = $('.system-status__charts-container');
        const categoryRendererConfig = [
            {
                categoryId: 'config',
                columns: [__('Status'), __('Description'), __('Date')],
                itemsMapper: ({ type, data: { details, date } }) => ({
                    type,
                    [`is${type}`]: true,
                    rows: [details, new Date(date * 1000).toLocaleString()],
                }),
                small: true,
            },
            {
                categoryId: 'config_values',
                columns: [__('Status'), __('Description'), __('Value')],
                itemsMapper: ({ type, message, data: { details } }) => ({
                    type,
                    [`is${type}`]: true,
                    rows: [details, message.replace(/\r?\n/g, '<br />')],
                }),
                small: true,
            },
            {
                categoryId: 'health',
                columns: [__('Status'), __('Description'), __('Details')],
                itemsMapper: ({ type, message, data: { details } }) => ({
                    type,
                    [`is${type}`]: true,
                    rows: [details, message.replace(/\r?\n/g, '<br />')],
                })
            },
            {
                categoryId: 'monitoring',
                columns: [__('Status'), __('Description'), __('Details')],
                itemsMapper: ({ type, message, data: { details } }) => ({
                    type,
                    [`is${type}`]: true,
                    rows: [details, message],
                })
            },
        ];

        return {
            categoriesRenderer: (categories) => {
                categoryRendererConfig.forEach(({ categoryId, columns, itemsMapper, small }) => {
                    const category = categories.find(({ id }) => id === categoryId);

                    if (!category) {
                        return;
                    }

                    const { items, title } = category;

                    tableRenderer({
                        category: title,
                        columns,
                        data: items.map(itemsMapper),
                        small,
                    })
                        .render($container);
                });
            },
            failedTasksDetailsRenderer: ({ children, data: { details } }) => {
                tableRenderer({
                    category: details,
                    columns: [__('Task'), __('Date'), ''],
                    data: children
                        .map(({ children, data: { task_label, task_report_time } }) => ({
                            detailsButton: children.length ? true : false,
                            reportData: JSON.stringify({ title: task_label, children }),
                            rows: [task_label, task_report_time],
                        })),
                })
                    .render($container);
            },
            processedTasksStatisticRenderer: ({ data: { details, P1D, P1W, P1M } }) => {
                timelineStatisticGraphRenderer({
                    category: details,
                    data: { P1D, P1W, P1M },
                    defaultInterval: 'P1D',
                    graphConfig: {
                        bindto: '.processed-tasks-statistic',
                        data: {
                            names: {
                                amount: __('Tasks processed'),
                                average: __('Average processing time, s'),
                            },
                        },
                    },
                    intervals: [
                        { label: __('Last Day'), value: 'P1D' },
                        { label: __('Last Week'), value: 'P1W' },
                        { label: __('Last Month'), value: 'P1M' }
                    ],
                    selector: 'processed-tasks-statistic',
                })
                    .render($container);
            },
            donutChartRenderer: ({ data: { check_id, details, value } }) => {
                donutChartRenderer({
                    category: details,
                    columns: [
                        [__('Free space'), 100 - value],
                        [__('Used space'), value],
                    ],
                    selector: check_id.replace(/\\/g, ''),
                })
                    .render($chartsContainer);
            },
            amountRenderer: ({ data: { details, report_value }}) => {
                const $taskQueue = $(amountRendererTpl({
                    category: details,
                    taskCount: report_value,
                }));

                $chartsContainer.append($taskQueue);
            },
        };
    });
