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

declare(strict_types=1);

namespace oat\taoSystemStatus\test\model\CheckStorage;

use oat\generis\persistence\PersistenceManager;
use oat\generis\test\PersistenceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\CheckStorage\RdsCheckStorage;
use oat\taoSystemStatus\test\model\Check\SampleInstanceCheck;

class RdsCheckStorageTest extends TestCase
{
    use PersistenceManagerMockTrait;

    public function testAddCheck()
    {
        $service = $this->getInstance();
        $this->assertTrue(
            $service->addCheck($this->getCheckMock(CheckInterface::TYPE_INSTANCE, ['foo' => 'bar']))
        );
        $checks = $service->getChecks(CheckInterface::TYPE_INSTANCE);
        $this->assertEquals(SampleInstanceCheck::class, $checks[0]->getId());
        $this->assertEquals(['foo' => 'bar'], $checks[0]->getParameters());
    }

    public function testAddCheckException()
    {
        $this->expectException('\oat\taoSystemStatus\model\SystemStatusException');
        $service = $this->getInstance();
        $service->addCheck($this->getCheckMock(CheckInterface::TYPE_INSTANCE, ['foo' => 'bar']));
        $service->addCheck($this->getCheckMock(CheckInterface::TYPE_INSTANCE, ['foo' => 'bar']));
    }

    public function testRemoveCheck()
    {
        $service = $this->getInstance();
        $check = $this->getCheckMock(CheckInterface::TYPE_INSTANCE, ['foo' => 'bar']);
        $this->assertTrue($service->addCheck($check));
        $this->assertCount(1, $service->getChecks(CheckInterface::TYPE_INSTANCE));
        $this->assertTrue($service->removeCheck($check));
        $this->assertCount(0, $service->getChecks(CheckInterface::TYPE_INSTANCE));
    }

    public function testGetCheck()
    {
        $service = $this->getInstance();
        $check = $this->getCheckMock(CheckInterface::TYPE_INSTANCE, ['foo' => 'bar']);
        $this->assertTrue($service->addCheck($check));
        $this->assertEquals($check, $service->getCheck($check->getId()));

    }

    public function testGetChecks()
    {
        $service = $this->getInstance();
        $this->assertCount(0, $service->getChecks(CheckInterface::TYPE_INSTANCE));
        $this->assertTrue(
            $service->addCheck($this->getCheckMock(CheckInterface::TYPE_INSTANCE, ['foo' => 'bar']))
        );
        $checks = $service->getChecks(CheckInterface::TYPE_INSTANCE);
        $this->assertCount(1, $checks);
        $this->assertEquals(SampleInstanceCheck::class, $checks[0]->getId());
        $this->assertEquals(['foo' => 'bar'], $checks[0]->getParameters());
        $this->assertCount(0, $service->getChecks(CheckInterface::TYPE_SYSTEM));
    }

    private function getInstance()
    {
        $persistenceManager = $this->getPersistenceManagerMock('testCheckStorage');
        $persistence = $persistenceManager->getPersistenceById('testCheckStorage');

        $service = new RdsCheckStorage('testCheckStorage');
        $service->install($persistence);
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());

        $config->set(PersistenceManager::SERVICE_ID, $persistenceManager);
        $serviceManager = new ServiceManager($config);
        $service->setServiceLocator($serviceManager);
        return $service;
    }

    /**
     * @param $type
     * @param $params
     * @return CheckInterface
     */
    private function getCheckMock($type, $params)
    {
        if ($type === CheckInterface::TYPE_INSTANCE) {
            return new SampleInstanceCheck($params);
        }
        if ($type === CheckInterface::TYPE_SYSTEM) {
            return new SampleSystemCheck($params);
        }
    }
}
