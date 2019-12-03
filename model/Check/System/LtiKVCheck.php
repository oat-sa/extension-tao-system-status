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
use oat\taoLti\models\classes\ResourceLink\KeyValueLink;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\user\KvLtiUserService;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use common_ext_ExtensionsManager;
use common_exception_Error;

/**
 * Class LtiKVCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class LtiKVCheck extends AbstractCheck
{

    /** @var Report */
    private $report;

    /**
     * @param array $params
     * @return Report
     * @throws common_exception_Error
     */
    public function __invoke($params = []): Report
    {
        $this->report = new Report(Report::TYPE_SUCCESS, __('Check LTI KV implementations'));

        $this->checkLtiLinkService();
        $this->checkLtiUserService();
        $this->checkLtiDeliveryExecutionService();

        if ($this->report->contains(Report::TYPE_WARNING)) {
            $this->report->setType(Report::TYPE_WARNING);
        }

        return $this->prepareReport($this->report);
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
        return __('System configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check if LTI services correctly configured to KV implementations');
    }

    /**
     * @return bool
     * @throws \common_exception_Error
     */
    private function checkLtiLinkService() : bool
    {
        $ltiLinkService = $this->getLtiLinkService();

        if ($ltiLinkService instanceof KeyValueLink) {
            $this->report->add(new Report(Report::TYPE_SUCCESS, __('KeyValue storage is configured for LtiLinkService')));
            return true;
        }

        $this->report->add(new Report(Report::TYPE_WARNING, __('KeyValue storage is not configured for LtiLinkService')));
        return false;
    }

    /**
     * @return bool
     * @throws common_exception_Error
     */
    private function checkLtiUserService() : bool
    {
        $ltiUserService = $this->getLtiUserService();

        if ($ltiUserService instanceof KvLtiUserService) {
            $this->report->add(new Report(Report::TYPE_SUCCESS, __('KeyValue storage is configured for LtiUserService')));
            return true;
        }

        $this->report->add(new Report(Report::TYPE_WARNING, __('KeyValue storage is not configured for LtiUserService')));
        return false;
    }

    /**
     * @return bool
     * @throws common_exception_Error
     */
    private function checkLtiDeliveryExecutionService() : bool
    {
        $ltiDeliveryExecutionService = $this->getLtiDeliveryExecutionService();

        if ($ltiDeliveryExecutionService instanceof KvLtiDeliveryExecutionService) {
            $this->report->add(new Report(Report::TYPE_SUCCESS, __('KeyValue storage is configured for LtiDeliveryExecutionService')));
            return true;
        }

        $this->report->add(new Report(Report::TYPE_WARNING, __('KeyValue storage is not configured for LtiDeliveryExecutionService')));
        return false;

    }

    /**
     * @return bool
     */
    private function checkInstalledLtiExtensions() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('taoLti') && $extensionManagerService->isInstalled('ltiDeliveryProvider');
    }

    /**
     * @return LinkService
     */
    private function getLtiLinkService() : LinkService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LinkService::SERVICE_ID);
    }

    /**
     * @return LtiUserService
     */
    private function getLtiUserService() : LtiUserService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LtiUserService::SERVICE_ID);
    }

    /**
     * @return common_ext_ExtensionsManager
     */
    private function getExtensionsManagerService() : common_ext_ExtensionsManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
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
