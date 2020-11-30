<?php
declare(strict_types=1);

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoSystemStatus\scripts\install;

use oat\oatbox\extension\AbstractAction;
use common_report_Report as Report;
use oat\taoSystemStatus\model\Alert\OpsgenieAlertService;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use oat\taoSystemStatus\model\SystemStatusException;

/**
 * Class RegisterOpsgenieAlertService
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package oat\taoSystemStatus\scripts\install
 */
class RegisterOpsgenieAlertService extends AbstractAction
{

    /**
     * @param $params
     * @return Report
     * @throws \oat\taoSystemStatus\model\SystemStatusException
     */
    public function __invoke($params)
    {
        if (!isset($params[0])) {
            return Report::createFailure('api key is not provided');
        }

        $key = $params[0];
        $service = new OpsgenieAlertService([
            OpsgenieAlertService::OPTION_API_KEY => $key
        ]);

        $this->registerService(OpsgenieAlertService::SERVICE_ID, $service);

        return new Report(Report::TYPE_SUCCESS, __('Opsgenie alerts service successfully registered'));
    }
}
