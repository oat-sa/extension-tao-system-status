<?php

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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\model\Check\System\AdvancedSearch;

use common_report_Report;
use oat\oatbox\reporting\Report;
use oat\tao\model\search\SearchProxy;
use oat\tao\elasticsearch\ElasticSearch;

class AdvancedSearchAvailabilityCheck extends AbstractAdvancedSearchCheck
{
    public function getDetails(): string
    {
        return __('Availability');
    }

    protected function doCheck(): common_report_Report
    {
        $advancedSearch = $this->getSearchProxy()->getAdvancedSearch();

        if ($advancedSearch instanceof ElasticSearch && $advancedSearch->ping()) {
            return Report::createSuccess(__('Available'));
        }

        return Report::createError(__('Unavailable'));
    }

    private function getSearchProxy(): SearchProxy
    {
        return $this->getContainer()->get(SearchProxy::SERVICE_ID);
    }
}
