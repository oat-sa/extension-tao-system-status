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
    'util/url',
    'c3'
], function ($, _, __, component, url, c3) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     * @private
     */
    var _defaults = {
        graphConfig : {
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
                    title: function (x, y) {
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
     * Get interval to build statistics
     * @return string
     */
    function getInterval() {
       return $('.js-tasks-statistics-interval').val();
    }

    function getData(period) {
        return $('.js-tasks-graph').data('statistics')[period];
    }

    /**
     * @param {Object} config
     * @param {String} [config.graphConfig] - configuration of c3 chart
     * @param {String} [config.autoRefresh] - interval of auto refresh
     * @param {String} [config.autoRefreshBar] - show auto refresh bar
     */
    function tasksGraphFactory(config) {
        var initConfig = _.merge({}, _defaults, config);
        var chart;
        var activityGraph = {
            /**
             * Refresh the graph
             * @param {Object} newConfig
             */
            refresh: function refresh(newConfig) {
                if (chart) {
                    initConfig = _.merge({}, initConfig, newConfig);
                    //there is no way to update graph with new config
                    chart.internal.config.axis_x_tick_format = initConfig.graphConfig.axis.x.tick.format;
                    chart.axis.labels({
                        x: initConfig.graphConfig.axis.x.label.text
                    });
                    chart.load(newConfig.graphConfig.data);
                }
            }
        };
        /**
         * Get chart config
         * @return {Object}
         */
        function getChartConfig() {
            var interval = getInterval();
            var newConfig;
            var data = getData(interval);
            newConfig = _.merge({}, _defaults, {
                graphConfig: {
                    data: {
                        json: data
                    },
                    axis: {
                        x : {
                            tick : {
                                format: interval === 'P1D' ? '%H:%M' : '%m-%d'
                            },
                            label : {
                                text:  interval === 'P1D' ? __('Hours') : __('Days')
                            }
                        }
                    }
                }
            });
            return newConfig;
        }

        $('.js-tasks-statistics-interval').on('change', function () {
            activityGraph.refresh(getChartConfig());
        });

        return component(activityGraph)
            .on('render', function() {
                $('.modal .js-tasks-graph-container').show();
                chart = c3.generate(getChartConfig().graphConfig);

            })
            .on('destroy', function() {
                $('.js-tasks-graph-container').hide();
                chart.destroy();
                chart = null;
            })
            .init(initConfig);
    }

    return tasksGraphFactory;
});
