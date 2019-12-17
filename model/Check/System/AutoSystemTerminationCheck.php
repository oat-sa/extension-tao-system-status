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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use common_ext_ExtensionException;
use Exception;
use oat\taoProctoring\model\FinishDeliveryExecutionsService;
use oat\taoProctoring\model\TerminateDeliveryExecutionsService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class AutoSystemTerminationCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class AutoSystemTerminationCheck extends AbstractCheck
{
    /**
     * @param array $params
     * @return Report
     * @throws Exception
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkAutoTerminationTime();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return true;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_SYSTEM;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('System configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check auto terminate/finish configuration.');
    }

    /**
     * @return Report
     * @throws Exception
     */
    private function checkAutoTerminationTime() : Report
    {
        $terminateTime = $this->getTerminateDeliveryExecutionsService()->getOption(TerminateDeliveryExecutionsService::OPTION_TTL_AS_ACTIVE);
        $finishTime = $this->getFinishDeliveryExecutionsService()->getOption(TerminateDeliveryExecutionsService::OPTION_TTL_AS_ACTIVE);
        if (!$terminateTime) {
            return new Report(Report::TYPE_WARNING, __('Auto terminating not correctly configured.'));
        }
        if (!$finishTime) {
            return new Report(Report::TYPE_WARNING, __('Auto finishing not correctly configured.'));
        }
        return new Report(Report::TYPE_SUCCESS,
            __(
                'Auto terminate will be every %s. Auto finish will be every %s',
                \tao_helpers_Date::displayInterval(new \DateInterval($terminateTime)),
                \tao_helpers_Date::displayInterval(new \DateInterval($finishTime))
            )
        );
    }

    /**
     * @return TerminateDeliveryExecutionsService
     */
    private function getTerminateDeliveryExecutionsService() : TerminateDeliveryExecutionsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(TerminateDeliveryExecutionsService::SERVICE_ID);
    }

    /**
     * @return FinishDeliveryExecutionsService
     */
    private function getFinishDeliveryExecutionsService() : FinishDeliveryExecutionsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(FinishDeliveryExecutionsService::SERVICE_ID);
    }
}
