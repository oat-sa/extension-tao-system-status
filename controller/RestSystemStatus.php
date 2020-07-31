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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 *
 */

namespace oat\taoSystemStatus\controller;

use common_report_Report as Report;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use oat\tao\model\http\HttpJsonResponseTrait;

/**
 * @OA\Info(title="Tao System Status API", version="0.1")
 */

/**
 * @OA\Get(
 *     path="/taoSystemStatus/api/systemStatus",
 *     summary="Get system status data",
 *     @OA\Response(
 *         response="200",
 *         description="Result of system check"
 *     )
 * )
 */
class RestSystemStatus extends \tao_actions_RestController
{
    use HttpJsonResponseTrait;

    /**
     * @return mixed
     */
    public function get()
    {
        /** @var Report[] $reports */
        $reports = $this->getSystemStatusService()->check();
        $this->setSuccessJsonResponse(['report' => $reports]);
    }

    /**
     * @return SystemStatusService
     */
    protected function getSystemStatusService() : SystemStatusService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
    }
}
