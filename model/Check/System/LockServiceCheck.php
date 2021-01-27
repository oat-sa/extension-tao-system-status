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
use oat\oatbox\mutex\LockService;
use oat\oatbox\mutex\NoLockStorage;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class LockServiceCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class LockServiceCheck extends AbstractCheck
{
    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $lockService = $this->getLockService();
        $storage = $lockService->getOption(LockService::OPTION_PERSISTENCE_CLASS);

        if ($storage === NoLockStorage::class) {
            return new Report(Report::TYPE_WARNING, __('Disabled'));
        }

        return new Report(Report::TYPE_SUCCESS, __('Enabled'));
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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Lock Service status');
    }

    /**
     * @return LockService
     */
    private function getLockService() : LockService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LockService::SERVICE_ID);
    }

}
