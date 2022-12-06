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
use oat\taoAdvancedSearch\model\Index\Report\IndexSummarizer;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use common_ext_ExtensionsManager;
use oat\oatbox\reporting\ReportInterface;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchIndexationCheck;

class AdvancedSearchIndexationCheckTest extends TestCase
{
    private AdvancedSearchIndexationCheck $sut;
    private AdvancedSearchChecker $advancedSearchChecker;
    private common_ext_ExtensionsManager $extensionManager;
    private IndexSummarizer $indexSummarizer;

    protected function setUp(): void
    {
        $this->extensionManager = $this->createMock(common_ext_ExtensionsManager::class);

        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);
        $this->indexSummarizer = $this->createMock(IndexSummarizer::class);

        $this->sut = new AdvancedSearchIndexationCheck();

        $map = [
            [common_ext_ExtensionsManager::SERVICE_ID, $this->extensionManager],
            [AdvancedSearchChecker::class, $this->advancedSearchChecker],
            [IndexSummarizer::class, $this->indexSummarizer],
        ];

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->any())
            ->method('get')
            ->willReturnMap($map);

        $serviceLocatorMock = $this->createMock(ServiceManager::class);
        $serviceLocatorMock->expects($this->any())->method('getContainer')->willReturn($containerMock);

        $this->sut->setServiceLocator(
            $serviceLocatorMock
        );
    }

    public function testGetDetails(): void
    {
        $this->assertEquals('Indexes population', $this->sut->getDetails());
    }

    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke(array $summary, string $expectedReportType, string $expectedReportMessage): void
    {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('ping')
            ->willReturn(true);

        $this->extensionManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn(['taoAdvancedSearch']);

        $this->indexSummarizer
            ->expects($this->once())
            ->method('summarize')
            ->willReturn($summary);

        $report = $this->sut->__invoke();

        $this->assertEquals($expectedReportType, $report->getType());
        $this->assertEquals($expectedReportMessage, $report->getMessage());
    }

    public function testInvokeWithDisabledAdvancedSearch(): void
    {
        $this->extensionManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn(['taoAdvancedSearch']);

        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->advancedSearchChecker
            ->expects($this->never())
            ->method('ping');

        $report = $this->sut->__invoke();

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals('Advanced search disabled', $report->getMessage());
    }

    public function testInvokeWithUnavailableAdvancedSearch(): void
    {
        $this->extensionManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn(['taoAdvancedSearch']);

        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('ping')
            ->willReturn(false);

        $report = $this->sut->__invoke();

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals('Advanced search unavailable', $report->getMessage());
    }

    public function invokeProvider(): array
    {
        return [
            'Indexes are populated' => [
                'summary' => [
                    [
                        'index' => 'index1',
                        'totalIndexed' => 100,
                        'totalInDb' => 100,
                        'percentageIndexed' => 100.0,
                    ],
                    [
                        'index' => 'index2',
                        'totalIndexed' => 0,
                        'totalInDb' => 0,
                        'percentageIndexed' => 0.0,
                    ],
                ],
                'expectedReportType' => ReportInterface::TYPE_SUCCESS,
                'expectedReportMessage' => 'Indexes are populated',
            ],
            'Some indexes are not populated (warning)' => [
                'summary' => [
                    [
                        'index' => 'index1',
                        'totalIndexed' => 100,
                        'totalInDb' => 100,
                        'percentageIndexed' => 100.0,
                    ],
                    [
                        'index' => 'index2',
                        'totalIndexed' => 0,
                        'totalInDb' => 100,
                        'percentageIndexed' => 98.0,
                    ],
                ],
                'expectedReportType' => ReportInterface::TYPE_WARNING,
                'expectedReportMessage' => 'Some indexes are not populated',
            ],
            'Some indexes are not populated (error)' => [
                'summary' => [
                    [
                        'index' => 'index1',
                        'totalIndexed' => 100,
                        'totalInDb' => 100,
                        'percentageIndexed' => 100.0,
                    ],
                    [
                        'index' => 'index2',
                        'totalIndexed' => 0,
                        'totalInDb' => 100,
                        'percentageIndexed' => 97.9,
                    ],
                ],
                'expectedReportType' => ReportInterface::TYPE_ERROR,
                'expectedReportMessage' => 'Indexes are not populated',
            ],
            'Indexes are not populated' => [
                'summary' => [
                    [
                        'index' => 'index1',
                        'totalIndexed' => 0,
                        'totalInDb' => 100,
                        'percentageIndexed' => 0.0,
                    ],
                    [
                        'index' => 'index2',
                        'totalIndexed' => 0,
                        'totalInDb' => 100,
                        'percentageIndexed' => 0.0,
                    ],
                ],
                'expectedReportType' => ReportInterface::TYPE_ERROR,
                'expectedReportMessage' => 'Indexes are not populated',
            ],
        ];
    }
}
