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
use common_ext_Extension;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class HeartBeatCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class HeartBeatCheck extends AbstractCheck
{
    /**
     * @param array $params
     * @return Report
     * @throws common_ext_ExtensionException
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkHeartBeat();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->ifTaoActIsInstalled();
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
        return __('Check heart beat timing.');
    }

    /**
     * @return Report
     * @throws common_ext_ExtensionException
     */
    private function checkHeartBeat() : Report
    {
        $config = $this->getTestRunnerService()->getConfig('testRunner');
        $heartbeatConfig = $config['plugins']['heartbeat'] ?? null;
        if (!$heartbeatConfig['frequency'] || $heartbeatConfig['frequency'] <= 20) {
            return new Report(Report::TYPE_WARNING, __('Heartbeats configured to %d seconds frequency. This may have negative impact on performance', $heartbeatConfig['frequency']));
        }
        return new Report(Report::TYPE_SUCCESS, __('Heart beat correctly configured.'));
    }

    /**
     * @return common_ext_Extension
     * @throws common_ext_ExtensionException
     */
    private function getTestRunnerService() : common_ext_Extension
    {
        return $this->getExtensionsManagerService()->getExtensionById('taoQtiTest');
    }

    /**
     * @return bool
     */
    private function ifTaoActIsInstalled() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('taoAct');
    }

}
