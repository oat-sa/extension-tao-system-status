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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\test\model\Check\System;

use oat\generis\test\PersistenceManagerMockTrait;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\Check\System\TaskQueueFinishedCheck;
use PHPUnit\Framework\TestCase;

class TaskQueueFinishedCheckTest extends TestCase
{
    use PersistenceManagerMockTrait;

    private TaskQueueFinishedCheck $subject;
    private TaskLogInterface $taskLogService;

    protected function setUp(): void
    {
        $this->subject = new TaskQueueFinishedCheck();

        $serviceLocatorMock = $this->createMock(ServiceManager::class);
        $this->taskLogService = $this->createMock(TaskLogInterface::class);

        $serviceLocatorMock->expects($this->once())
            ->method('has')
            ->with(TaskLogInterface::SERVICE_ID)
            ->willReturn(true);

        $serviceLocatorMock->expects($this->once())
            ->method('get')
            ->with(TaskLogInterface::SERVICE_ID)
            ->willReturn($this->taskLogService);

        $this->subject->setServiceLocator($serviceLocatorMock);
    }

    public function testDoCheck(): void
    {
        $this->taskLogService->expects($this->any())
            ->method('getTaskExecutionTimesByDateRange')
            ->willReturn([]);

        $data = (($this->subject)())->getData();

        $this->assertCount(7, $data);

        $this->assertArrayHasKey('P1D', $data);
        $this->assertCount(3, $data['P1D']);

        $this->assertArrayHasKey('time', $data['P1D']);
        $this->assertArrayHasKey('average', $data['P1D']);
        $this->assertArrayHasKey('amount', $data['P1D']);
        $this->assertCount(144, $data['P1D']['time']);
        $this->assertCount(144, $data['P1D']['average']);
        $this->assertCount(144, $data['P1D']['amount']);

        $this->assertArrayHasKey('P1W', $data);
        $this->assertCount(3, $data['P1W']);

        $this->assertArrayHasKey('time', $data['P1W']);
        $this->assertArrayHasKey('average', $data['P1W']);
        $this->assertArrayHasKey('amount', $data['P1W']);
        $this->assertCount(168, $data['P1W']['time']);
        $this->assertCount(168, $data['P1W']['average']);
        $this->assertCount(168, $data['P1W']['amount']);

        $this->assertArrayHasKey('P1M', $data);
        $this->assertCount(3, $data['P1M']);

        $this->assertArrayHasKey('time', $data['P1M']);
        $this->assertArrayHasKey('average', $data['P1M']);
        $this->assertArrayHasKey('amount', $data['P1M']);
        $this->assertCount(186, $data['P1M']['time']);
        $this->assertCount(186, $data['P1M']['average']);
        $this->assertCount(186, $data['P1M']['amount']);

        $this->assertArrayHasKey(CheckInterface::PARAM_CATEGORY, $data);
        $this->assertEquals(__('Monitoring / Statistics'), $data[CheckInterface::PARAM_CATEGORY]);
        $this->assertArrayHasKey(CheckInterface::PARAM_DETAILS, $data);
        $this->assertEquals(__('Statistics of processed tasks'), $data[CheckInterface::PARAM_DETAILS]);
        $this->assertArrayHasKey(CheckInterface::PARAM_CHECK_ID, $data);
        $this->assertEquals(get_class($this->subject), $data[CheckInterface::PARAM_CHECK_ID]);
        $this->assertArrayHasKey(CheckInterface::PARAM_DATE, $data);
    }
}
