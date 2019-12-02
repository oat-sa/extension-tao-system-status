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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoSystemStatus\scripts\install;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\AbstractAction;
use oat\taoSystemStatus\model\SystemStatus\InstanceStatusService;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use common_report_Report as Report;

/**
 * Class RegisterCheckStorage
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package oat\taoSystemStatus\scripts\install
 */
class RegisterCheckStorage extends AbstractAction
{
    public function __invoke($params)
    {
        $this->installInstanceStatusStorage();
        $this->installSystemStatusStorage();
        return new Report(Report::TYPE_SUCCESS, __('System status storage successfully installed'));
    }

    private function installInstanceStatusStorage()
    {
        $instanceStatusService = $this->getServiceManager()->get(InstanceStatusService::SERVICE_ID);
        $persistenceManager = $this->getServiceManager()->get(PersistenceManager::SERVICE_ID);
        $instanceStatusService->getOption(InstanceStatusService::OPTION_STORAGE_PERSISTENCE);
        $storageClass = $instanceStatusService->getOption(InstanceStatusService::OPTION_STORAGE_CLASS);
        $persistenceId = $instanceStatusService->getOption(InstanceStatusService::OPTION_STORAGE_PERSISTENCE);
        $persistence = $persistenceManager->getPersistenceById($persistenceId);
        $storage = new $storageClass($persistenceId);
        $storage->install($persistence);
    }

    private function installSystemStatusStorage()
    {
        $systemStatusService = $this->getServiceManager()->get(SystemStatusService::SERVICE_ID);
        $persistenceManager = $this->getServiceManager()->get(PersistenceManager::SERVICE_ID);
        $systemStatusService->getOption(SystemStatusService::OPTION_STORAGE_PERSISTENCE);
        $storageClass = $systemStatusService->getOption(SystemStatusService::OPTION_STORAGE_CLASS);
        $persistenceId = $systemStatusService->getOption(SystemStatusService::OPTION_STORAGE_PERSISTENCE);
        $persistence = $persistenceManager->getPersistenceById($persistenceId);
        $storage = new $storageClass($persistenceId);
        $storage->install($persistence);
    }

}
