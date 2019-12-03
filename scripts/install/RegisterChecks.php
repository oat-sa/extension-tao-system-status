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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoSystemStatus\scripts\install;

use oat\oatbox\extension\AbstractAction;
use common_report_Report as Report;
use oat\taoSystemStatus\model\SystemStatus\InstanceStatusService;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

/**
 * Class RegisterChecks
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package oat\taoSystemStatus\scripts\install
 */
class RegisterChecks extends AbstractAction
{
    /**
     * @param $params
     * @return Report
     * @throws \oat\taoSystemStatus\model\SystemStatusException
     */
    public function __invoke($params)
    {
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        /** @var InstanceStatusService $systemStatusService */
        $instanceStatusService = $this->getServiceLocator()->get(InstanceStatusService::SERVICE_ID);

        foreach ($this->getSystemChecks() as $check) {
            $systemStatusService->addCheck($check);
        }
        foreach ($this->getInstanceChecks() as $check) {
            $instanceStatusService->addCheck($check);
        }

        return new Report(Report::TYPE_SUCCESS, __('System status checks successfully registered.'));
    }

    private function getSystemChecks(): array
    {
        return [
            new \oat\taoSystemStatus\model\Check\System\FrontEndLogCheck([]),
            new \oat\taoSystemStatus\model\Check\System\TaoLtiKVCheck([]),
            new \oat\taoSystemStatus\model\Check\System\TaoLtiDeliveryKVCheck([]),
            new \oat\taoSystemStatus\model\Check\System\WkhtmltopdfCheck([])
        ];
    }

    private function getInstanceChecks(): array
    {
        return [
        ];
    }
}
