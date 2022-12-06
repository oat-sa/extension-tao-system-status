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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\test\model\Check\System\AdvancedSearch;

use oat\oatbox\service\ServiceManager;
use PHPUnit\Framework\TestCase;
use common_ext_ExtensionsManager;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchAvailabilityCheck;
use Psr\Container\ContainerInterface;

class AdvancedSearchAvailabilityCheckTest extends TestCase
{
    private AdvancedSearchAvailabilityCheck $sut;
    private AdvancedSearchChecker $advancedSearchChecker;
    private common_ext_ExtensionsManager $extensionManager;

    protected function setUp(): void
    {
        $this->sut = new AdvancedSearchAvailabilityCheck();

        $this->extensionManager = $this->createMock(common_ext_ExtensionsManager::class);
        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);

        $map = [
            [common_ext_ExtensionsManager::SERVICE_ID, $this->extensionManager],
            [AdvancedSearchChecker::class, $this->advancedSearchChecker]
        ];

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')->willReturnMap($map);

        $serviceLocatorMock = $this->createMock(ServiceManager::class);
        $serviceLocatorMock->method('getContainer')->willReturn($containerMock);

        $this->sut->setServiceLocator($serviceLocatorMock);
    }

    public function testGetDetails(): void
    {
        $this->assertEquals('Availability', $this->sut->getDetails());
    }

    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke(
        bool   $advancedSearchPing,
        string $expectedReportType,
        string $expectedReportMessage
    ): void {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('ping')
            ->willReturn($advancedSearchPing);

        $this->extensionManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn(['taoAdvancedSearch']);

        $report = $this->sut->__invoke();

        $this->assertEquals($expectedReportType, $report->getType());
        $this->assertEquals($expectedReportMessage, $report->getMessage());
    }

    public function invokeProvider(): array
    {
        return [
            'Available' => [
                'advancedSearchPing' => true,
                'expectedReportType' => ReportInterface::TYPE_SUCCESS,
                'expectedReportMessage' => 'Available',
            ],
            'Unavailable' => [
                'advancedSearchPing' => false,
                'expectedReportType' => ReportInterface::TYPE_ERROR,
                'expectedReportMessage' => 'Unavailable',
            ],
        ];
    }
}
