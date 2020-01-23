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
use oat\ltiDeliveryProvider\model\actions\GetActiveDeliveryExecution;
use oat\tao\model\actionQueue\implementation\InstantActionQueue;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class LoginQueueCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class LoginQueueCheck extends AbstractCheck
{
    const CATEGORY_ID = 'config_values';

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkLoginQueue();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->checkInstalledLtiDeliveryProviderExtension();
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
        return __('Configuration Values');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Login Queue service configuration');
    }

    /**
     * @return Report
     */
    private function checkLoginQueue() : Report
    {
        $loginQueueActions = $this->getInstantActionQueue()->getOption(InstantActionQueue::OPTION_ACTIONS);

        if (!isset($loginQueueActions[GetActiveDeliveryExecution::class])) {
            return new Report(Report::TYPE_WARNING, __('Action GetActiveDeliveryExecution for Login Queue is not exists.'));
        }
        $restrictions = $loginQueueActions[GetActiveDeliveryExecution::class]['restrictions'];
        $restrictionsOptions = '';
        foreach ($restrictions as $restriction => $property) {
            $restrictionName = explode('\\', $restriction);
            $restrictionsOptions .= end($restrictionName) .'='.json_encode($property) . PHP_EOL;
        }
        return new Report(Report::TYPE_INFO, __('Implementation: GetActiveDeliveryExecution') . PHP_EOL . __('Options:') . ' '. $restrictionsOptions);

    }

    /**
     * @return bool
     */
    private function checkInstalledLtiDeliveryProviderExtension() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('ltiDeliveryProvider');
    }

    /**
     * @return InstantActionQueue
     */
    private function getInstantActionQueue() : InstantActionQueue
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(InstantActionQueue::SERVICE_ID);
    }

}
