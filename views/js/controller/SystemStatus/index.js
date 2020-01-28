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
    'i18n',
    'taoSystemStatus/dataProvider/reportsDataProvider',
    'taoSystemStatus/checkRenderers/renderers',
    'layout/loading-bar',
    'ui/feedback',
], function (
    __,
    reportsDataProvider,
    renderers,
    loadingBar,
    feedback,
) {
        'use strict';

        return {
            start: function () {
                loadingBar.start();

                reportsDataProvider.getReports()
                    .then(({ categories, checksWithCustomRenderer }) => {
                        renderers.categoriesRenderer(categories);

                        checksWithCustomRenderer.forEach(check => {
                            const {  data: { renderer } } = check;

                            renderers[renderer](check);
                        });
                    })
                    .catch(() => feedback().error(__('Something went wrong.')))
                    .finally(() => loadingBar.stop());
            }
        }
    });
