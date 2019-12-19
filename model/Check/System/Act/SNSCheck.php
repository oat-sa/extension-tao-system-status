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

namespace oat\taoSystemStatus\model\Check\System\Act;

use common_report_Report as Report;
use common_ext_ExtensionException;
use oat\taoAct\model\message\AmazonSimpleNotificationService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class SNSCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class SNSCheck extends AbstractCheck
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
        $report = $this->checkSNS();

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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('SNS messaging service configuration');
    }

    /**
     * @return Report
     */
    private function checkSNS() : Report
    {
        $snsOptions = $this->getSnsService()->getOptions();
        if ($search = array_keys($snsOptions, AmazonSimpleNotificationService::ARN_NOT_CONFIGURED_VALUE)) {
            return new Report(Report::TYPE_ERROR, __('SNS messaging service is not correctly configured. The following options have default values: ' . implode(', ', $search)));
        }

        return new Report(Report::TYPE_SUCCESS, __('SNS messaging service is configured correctly'));
    }

    /**
     * @return AmazonSimpleNotificationService
     */
    private function getSnsService() : AmazonSimpleNotificationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(AmazonSimpleNotificationService::SERVICE_ID);
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
