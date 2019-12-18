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
use Exception;
use oat\taoProctoring\model\implementation\DeliveryExecutionStateService;
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
       return $this->ifTaoProctoringIsInstalled();
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
        return __('Check auto cancellation/termination configuration.');
    }

    /**
     * @return Report
     * @throws Exception
     */
    private function checkAutoTerminationTime() : Report
    {
        $cancellationDelay = $this->getDeliveryExecutionStateService()->getOption(DeliveryExecutionStateService::OPTION_CANCELLATION_DELAY);
        $terminationDelay = $this->getDeliveryExecutionStateService()->getOption(DeliveryExecutionStateService::OPTION_TERMINATION_DELAY_AFTER_PAUSE);

        if (!$cancellationDelay) {
            return new Report(Report::TYPE_WARNING, __('Auto cancellation not correctly configured.'));
        }
        if (!$terminationDelay) {
            return new Report(Report::TYPE_WARNING, __('Auto termination after pause not correctly configured.'));
        }
        return new Report(Report::TYPE_SUCCESS,
            __(
                'Auto cancellation will be every %s. Auto termination after pause will be every %s',
                \tao_helpers_Date::displayInterval(new \DateInterval($cancellationDelay)),
                \tao_helpers_Date::displayInterval(new \DateInterval($terminationDelay))
            )
        );
    }

    /**
     * @return DeliveryExecutionStateService
     */
    private function getDeliveryExecutionStateService() : DeliveryExecutionStateService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(DeliveryExecutionStateService::SERVICE_ID);
    }

    /**
     * @return bool
     */
    private function ifTaoProctoringIsInstalled() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('taoProctoring');
    }
}