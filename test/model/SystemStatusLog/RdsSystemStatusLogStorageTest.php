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
use common_report_Report as Report;
use oat\taoSystemStatus\model\SystemStatusLog\RdsSystemStatusLogStorageStorage;
use oat\taoSystemStatus\test\model\Check\SampleInstanceCheck;


class RdsSystemStatusLogStorageTest extends TestCase
{
    use PersistenceManagerMockTrait;

    public function testLog()
    {
        $service = $this->getInstance();
        $report = new Report(Report::TYPE_INFO);
        $check = new SampleInstanceCheck();
        $this->assertTrue($service->log($check, $report, 'testInstance'));
        $checks = $service->getLatest(new \DateInterval('PT1M'));
        $this->assertEquals($check->getId(), $checks[0][RdsSystemStatusLogStorageStorage::COLUMN_CHECK_ID]);
        $this->assertEquals(json_encode($report), $checks[0][RdsSystemStatusLogStorageStorage::COLUMN_REPORT]);
    }

    public function testGetLatest()
    {
        $check = new SampleInstanceCheck();
        $report1 = new Report(Report::TYPE_INFO, 'message1');
        $report2 = new Report(Report::TYPE_INFO, 'message2');
        $report3 = new Report(Report::TYPE_INFO, 'message3');
        $service = $this->getInstance();

        $checks = $service->getLatest(new \DateInterval('PT1M'));
        $this->assertCount(0,$checks);

        $this->assertTrue($service->log($check, $report1, 'instance1'));
        $this->assertTrue($service->log($check, $report2, 'instance1'));
        $checks = $service->getLatest(new \DateInterval('PT1M'));
        $this->assertEquals($check->getId(), $checks[0][RdsSystemStatusLogStorageStorage::COLUMN_CHECK_ID]);
        $this->assertEquals(json_encode($report2), $checks[0][RdsSystemStatusLogStorageStorage::COLUMN_REPORT]);
        $this->assertCount(1,$checks);

        $this->assertTrue($service->log($check, $report3, 'instance2'));
        $checks = $service->getLatest(new \DateInterval('PT1M'));
        $this->assertCount(2,$checks);
        sleep(2);
        $this->assertCount(0, $service->getLatest(new \DateInterval('PT1S')));
    }


    private function getInstance()
    {
        $persistenceManager = $this->getPersistenceManagerMock('testSystemStatusLogStorage');
        $persistence = $persistenceManager->getPersistenceById('testSystemStatusLogStorage');
        $service = new RdsSystemStatusLogStorageStorage('testSystemStatusLogStorage');
        $service->install($persistence);
        $config = new \common_persistence_KeyValuePersistence([], new \common_persistence_InMemoryKvDriver());
        $config->set(PersistenceManager::SERVICE_ID, $persistenceManager);
        $serviceManager = new ServiceManager($config);
        $service->setServiceLocator($serviceManager);
        return $service;
    }
}
