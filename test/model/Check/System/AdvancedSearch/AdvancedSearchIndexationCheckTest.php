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

use Psr\Log\LoggerInterface;
use oat\generis\test\TestCase;
use common_ext_ExtensionsManager;
use oat\oatbox\log\LoggerService;
use oat\oatbox\reporting\ReportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\taoAdvancedSearch\model\Index\Report\IndexSummarizer;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchIndexationCheck;

class AdvancedSearchIndexationCheckTest extends TestCase
{
    /** @var AdvancedSearchIndexationCheck */
    private $sut;

    /** @var AdvancedSearchChecker|MockObject */
    private $advancedSearchChecker;

    /** @var IndexSummarizer|MockObject */
    private $indexSummarizer;

    /** @var MockObject|LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $extensionManager = $this->createMock(common_ext_ExtensionsManager::class);
        $extensionManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn(['taoAdvancedSearch']);

        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);
        $this->indexSummarizer = $this->createMock(IndexSummarizer::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new AdvancedSearchIndexationCheck();
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    common_ext_ExtensionsManager::SERVICE_ID => $extensionManager,
                    AdvancedSearchChecker::class => $this->advancedSearchChecker,
                    IndexSummarizer::class => $this->indexSummarizer,
                    LoggerService::SERVICE_ID => $this->logger,
                ]
            )
        );
    }

    public function testGetDetails(): void
    {
        $this->assertEquals('Indexes population', $this->sut->getDetails());
    }

    /**
     * @dataProvider invokeProvider
     */
    public function testInvoke(
        array $summary,
        string $expectedReportType,
        string $expectedReportMessage,
        int $logCount
    ): void {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('ping')
            ->willReturn(true);

        $this->indexSummarizer
            ->expects($this->once())
            ->method('summarize')
            ->willReturn($summary);

        $this->logger
            ->expects($this->exactly($logCount))
            ->method('warning');

        $report = $this->sut->__invoke();

        $this->assertEquals($expectedReportType, $report->getType());
        $this->assertEquals($expectedReportMessage, $report->getMessage());
    }

    public function testInvokeWithDisabledAdvancedSearch(): void
    {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->advancedSearchChecker
            ->expects($this->never())
            ->method('ping');

        $this->indexSummarizer
            ->expects($this->never())
            ->method('summarize');

        $this->logger
            ->expects($this->never())
            ->method('warning');

        $report = $this->sut->__invoke();

        $this->assertEquals(ReportInterface::TYPE_ERROR, $report->getType());
        $this->assertEquals('Advanced search disabled', $report->getMessage());
    }

    public function testInvokeWithUnavailableAdvancedSearch(): void
    {
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->advancedSearchChecker
            ->expects($this->once())
            ->method('ping')
            ->willReturn(false);

        $this->indexSummarizer
            ->expects($this->never())
            ->method('summarize');

        $this->logger
            ->expects($this->never())
            ->method('warning');

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
                'logCount' => 0,
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
                'logCount' => 1,
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
                'logCount' => 1,
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
                'logCount' => 2,
            ],
        ];
    }
}
