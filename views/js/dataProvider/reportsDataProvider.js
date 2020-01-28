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
    'util/url',
    'core/request',
    'json!./data.json'
], function (urlHelper, request, data) {
    const mapCategories = (acc, item, i, array) => {
        const {
            data: {
                category: categoryTitle,
                category_id: categoryId
            }
        } = item;
        const category = acc.find(({ id }) => categoryId === id)

        if (!category) {
            acc.push({
                id: categoryId,
                items: array.filter(({ data: { category_id: id } }) => id === categoryId),
                title: categoryTitle,
            });
        }

        return acc
    }

    const responseMapper = (({ report: { children: reports } }) => ({
        categories: reports
            .filter(({ data: { renderer } }) => !renderer)
            .reduce(mapCategories, []),
        checksWithCustomRenderer: reports
            .filter(({ data: { renderer } }) => renderer),
    }));

    return {
        getReports: () => {
            return new Promise((resolve) => resolve(data)).then(responseMapper);

            // return request({
            //     url: urlHelper.route('reports', 'SystemStatus', 'taoSystemStatus'),
            //     method: 'GET'
            // });
        }
    };
})
