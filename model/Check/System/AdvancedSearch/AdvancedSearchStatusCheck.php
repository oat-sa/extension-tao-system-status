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
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;

class AdvancedSearchStatusCheck extends AbstractAdvancedSearchCheck
{
    public function getDetails(): string
    {
        return __('Is enabled');
    }

    protected function doCheck(): common_report_Report
    {
        if ($this->getAdvancedSearchChecker()->isEnabled()) {
            return Report::createSuccess(__('Yes'));
        }

        return Report::createError(__('No'));
    }

    private function getAdvancedSearchChecker(): AdvancedSearchChecker
    {
        return $this->getContainer()->get(AdvancedSearchChecker::class);
    }
}
