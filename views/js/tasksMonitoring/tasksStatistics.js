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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'c3',
    'tpl!taoSystemStatus/tasksMonitoring/tasksStatistics'
], function ($, _, __, component, c3, template) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     * @private
     */
    const _defaults = {
        graphConfig: {
            bindto: '.js-tasks-graph',
            padding: {
                bottom: 0,
                left: 0
            },
            data: {
                x: 'time',
                xFormat: '%Y-%m-%d %H:%M:%S',
                mimeType: 'json',
                type: 'line',
                names: {
                    amount: __('Tasks processed'),
                    average: __('Average processing time, s')
                }
            },
            tooltip: {
                format: {
                    title: function (x) {
                        return new Date(Date.parse(x)).toUTCString();
                    }
                }
            },
            axis: {
                x: {
                    type: 'timeseries',
                    tick: {
                        format: '%H:%M'
                    },
                    label: {
                        position: 'bottom center'
                    }
                },
                y: {
                    inner: true,
                    label: {
                        position: 'outer-top',
                    }
                }
            }
        }
    };

    /**
     * Get chart config
     * @return {Object}
     */
    const getChartConfig = (interval, data) => _.merge({}, _defaults, {
        graphConfig: {
            data: {
                json: data
            },
            axis: {
                x: {
                    tick: {
                        format: interval === 'P1D' ? '%H:%M' : '%m-%d'
                    },
                    label: {
                        text: interval === 'P1D' ? __('Hours') : __('Days')
                    }
                }
            }
        }
    });

    /**
     * @param {Object} config
     */
    function tasksGraphFactory(config) {
        let initConfig = _.merge(
            {},
            getChartConfig(config.defaultInterval, config.data[config.defaultInterval]),
            config,
        );
        let chart;

        const activityGraph = {
            /**
             * Refresh the graph
             * @param {Object} newConfig
             */
            refresh: function refresh(interval, data) {
                const newConfig = getChartConfig(interval, data);

                initConfig = _.merge({}, initConfig, newConfig);
                //there is no way to update graph with new config
                chart.internal.config.axis_x_tick_format = initConfig.graphConfig.axis.x.tick.format;
                chart.axis.labels({
                    x: initConfig.graphConfig.axis.x.label.text
                });
                chart.load(newConfig.graphConfig.data);
            },
        };

        return component(activityGraph)
            .setTemplate(template)
            .on('render', function () {
                const $element = this.getElement();
                const $intervalSelect = $element.find('.statistics-chart__interval-select');

                chart = c3.generate(this.config.graphConfig);

                $intervalSelect.on('change',
                    ({ target: { value } }) => this.refresh(value, this.config.data[value])
                );
            })
            .on('destroy', function () {
                chart.destroy();
                chart = null;
            })
            .init(initConfig);
    }

    return tasksGraphFactory;
});
