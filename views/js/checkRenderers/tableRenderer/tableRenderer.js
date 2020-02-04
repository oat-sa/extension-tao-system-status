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
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'tpl!taoSystemStatus/checkRenderers/tableRenderer/tableRenderer',
    'ui/modal',
], function ($, _, __, component, template) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     * @private
     */
    const _defaults = {};

    /**
     * @param {Object} config
     */
    function tasksGraphFactory(config) {
        let initConfig = _.merge(
            {},
            _defaults,
            config,
        );

        return component({})
            .setTemplate(template)
            .on('render', function () {
                const $element = this.getElement();
                const $container = this.getContainer();

                $('.details-button', $element)
                    .on('click', ({ target }) => {
                        const { title, children } = $(target).data('report');
                        const $modal = $('<div class="modal system-status-table__details-modal"></div>');
                        const $reportDetails = $(template({
                            category: `"${title}" ${__('details')}`,
                            columns: [__('Status'), __('Description')],
                            data: children
                                .map(({ type, message }) => ({
                                    type,
                                    [`is${type}`]: true,
                                    rows: [message.replace(/\r?\n/g, '<br />')],
                                }))
                        }));

                        $modal.append($reportDetails);
                        $container.append($modal);
                        $modal.modal({
                            minWidth: 450,
                            startClosed: true,
                            top: 100,
                        });
                        $modal.modal('open');

                        $modal.on('closed.modal', function () {
                            $modal.modal('destroy');
                            $(this).remove();
                        });
                    });
            })
            .init(initConfig);
    }

    return tasksGraphFactory;
});
