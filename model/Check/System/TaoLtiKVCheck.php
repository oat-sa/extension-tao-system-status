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
use oat\taoLti\models\classes\ResourceLink\KeyValueLink;
use oat\taoLti\models\classes\ResourceLink\LinkService;
use oat\taoLti\models\classes\user\KvLtiUserService;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use common_ext_ExtensionsManager;

/**
 * Class TaoLtiKVCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class TaoLtiKVCheck extends AbstractCheck
{

    /** @var Report */
    private $report;

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        $this->report = new Report(Report::TYPE_SUCCESS);

        if (!$this->checkLtiLinkService()) {
            $this->report->setType(Report::TYPE_WARNING);
        }

        if (!$this->checkLtiUserService()) {
            $this->report->setType(Report::TYPE_WARNING);
        }

        return $this->prepareReport($this->report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->checkInstalledTaoLtiExtension();
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
        return __('Check if taoLTI services correctly configured to KV implementations');
    }

    /**
     * @return bool
     */
    private function checkLtiLinkService() : bool
    {
        $ltiLinkService = $this->getLtiLinkService();

        if ($ltiLinkService instanceof KeyValueLink) {
            $this->report->setMessage($this->getMessageFromReport() . __('KeyValue storage is configured for LtiLinkService.'));
            return true;
        }

        $this->report->setMessage($this->getMessageFromReport() . __('KeyValue storage is not configured for LtiLinkService.'));
        return false;
    }

    /**
     * @return bool
     */
    private function checkLtiUserService() : bool
    {
        $ltiUserService = $this->getLtiUserService();

        if ($ltiUserService instanceof KvLtiUserService) {
            $this->report->setMessage($this->getMessageFromReport() . __('KeyValue storage is configured for LtiUserService.'));
            return true;
        }
        $this->report->setMessage($this->getMessageFromReport()  . __('KeyValue storage is not configured for LtiUserService.'));
        return false;
    }

    /**
     * @return string
     */
    private function getMessageFromReport() : string
    {
        return $this->report->getMessage() ? $this->report->getMessage() . ' ' : '';
    }

    /**
     * @return bool
     */
    private function checkInstalledTaoLtiExtension() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('taoLti');
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
}
