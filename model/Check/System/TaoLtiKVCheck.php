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

/**
 * Class TaoLtiKVCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class TaoLtiKVCheck extends AbstractCheck
{

    const CATEGORY_ID = 'config';

    /** @var Report */
    private $report;

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('LTI Link and Lti User Service configuration');
    }

    /**
     * @return bool
     */
    private function checkLtiLinkService() : bool
    {
        $ltiLinkService = $this->getLtiLinkService();

        if ($ltiLinkService instanceof KeyValueLink) {
            $this->report->setMessage($this->getMessageFromReport() . __('The LTI Link Service (\'taoLti/ResourceLink\') is configured correctly.'));
            return true;
        }

        $this->report->setMessage($this->getMessageFromReport() . __('The LTI Link Service (\'taoLti/ResourceLink\') is not configured optimally. There may be performance issues.').PHP_EOL);
        return false;
    }

    /**
     * @return bool
     */
    private function checkLtiUserService() : bool
    {
        $ltiUserService = $this->getLtiUserService();

        if ($ltiUserService instanceof KvLtiUserService) {
            $this->report->setMessage($this->getMessageFromReport() . __('The LTI User Service (\'taoLti/LtiUserService\') is configured correctly.'));
            return true;
        }
        $this->report->setMessage($this->getMessageFromReport()  . __('The LTI User Service (\'taoLti/LtiUserService\') is not configured optimally. There may be performance issues.').PHP_EOL);
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
}
