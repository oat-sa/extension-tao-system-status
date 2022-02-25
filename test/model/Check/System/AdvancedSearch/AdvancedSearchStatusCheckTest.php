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

use oat\generis\test\TestCase;
use common_ext_ExtensionsManager;
use oat\oatbox\reporting\ReportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchStatusCheck;

class AdvancedSearchStatusCheckTest extends TestCase
{
    /** @var AdvancedSearchStatusCheck */
    private $sut;

    /** @var AdvancedSearchChecker|MockObject */
    private $advancedSearchChecker;

    protected function setUp(): void
    {
        $extensionManager = $this->createMock(common_ext_ExtensionsManager::class);
        $extensionManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn(['taoAdvancedSearch']);

        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);

        $this->sut = new AdvancedSearchStatusCheck();
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    common_ext_ExtensionsManager::SERVICE_ID => $extensionManager,
                    AdvancedSearchChecker::class => $this->advancedSearchChecker,
                ]
            )
        );
    }

    public function testGetDetails(): void
    {
        $this->assertEquals('Status', $this->sut->getDetails());
    }

    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke(
        bool $isAdvancedSearchEnabled,
        string $expectedReportType,
        string $expectedReportMessage
    ): void {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isAdvancedSearchEnabled);

        $report = $this->sut->__invoke();

        $this->assertEquals($expectedReportType, $report->getType());
        $this->assertEquals($expectedReportMessage, $report->getMessage());
    }

    public function invokeProvider(): array
    {
        return [
            'Enabled' => [
                'isAdvancedSearchEnabled' => true,
                'expectedReportType' => ReportInterface::TYPE_SUCCESS,
                'expectedReportMessage' => 'Enabled',
            ],
            'Disabled' => [
                'isAdvancedSearchEnabled' => false,
                'expectedReportType' => ReportInterface::TYPE_ERROR,
                'expectedReportMessage' => 'Disabled',
            ],
        ];
    }
}
