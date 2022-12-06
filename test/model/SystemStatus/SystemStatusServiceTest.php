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
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\test\model\SystemStatus;

use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

class SystemStatusServiceTest extends TestCase
{
    public function testGetInstanceId()
    {
        $instanceId = $this->getInstance()->getInstanceId();
        $this->assertEquals($instanceId, $this->getInstance()->getInstanceId());
    }

    private function getInstance(): SystemStatusService
    {
        $service = new SystemStatusService([]);
        $service->setServiceLocator(ServiceManager::getServiceManager());
        return $service;
    }
}
