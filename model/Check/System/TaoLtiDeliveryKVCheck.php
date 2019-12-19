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
use oat\ltiDeliveryProvider\model\execution\implementation\KvLtiDeliveryExecutionService;
use oat\ltiDeliveryProvider\model\execution\LtiDeliveryExecutionService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class TaoLtiDeliveryKVCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class TaoLtiDeliveryKVCheck extends AbstractCheck
{

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkLtiDeliveryExecutionService();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->checkInstalledLtiExtensions();
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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Lti Delivery Execution Service configuration');
    }

    /**
     * @return Report
     */
    private function checkLtiDeliveryExecutionService() : Report
    {
        $ltiDeliveryExecutionService = $this->getLtiDeliveryExecutionService();

        if ($ltiDeliveryExecutionService instanceof KvLtiDeliveryExecutionService) {
            return new Report(Report::TYPE_SUCCESS, __('The LTI Delivery Service (\'ltiDeliveryProvider/LtiDeliveryExecution\') is configured correctly.'));
        }

        return new Report(Report::TYPE_WARNING, __('The LTI Delivery Service (\'ltiDeliveryProvider/LtiDeliveryExecution\') is not configured optimally. There may be performance issues.'));

    }

    /**
     * @return bool
     */
    private function checkInstalledLtiExtensions() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('ltiDeliveryProvider');
    }

    /**
     * @return LtiDeliveryExecutionService
     */
    private function getLtiDeliveryExecutionService() : LtiDeliveryExecutionService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiDeliveryExecutionService::SERVICE_ID);
    }
}
