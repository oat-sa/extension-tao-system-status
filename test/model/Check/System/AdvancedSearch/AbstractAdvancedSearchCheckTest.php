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

use common_report_Report;
use oat\generis\test\TestCase;
use oat\oatbox\reporting\Report;
use common_ext_ExtensionsManager;
use PHPUnit\Framework\MockObject\MockObject;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AbstractAdvancedSearchCheck;

class AbstractAdvancedSearchCheckTest extends TestCase
{
    /** @var AbstractAdvancedSearchCheck */
    private $sut;

    /** @var common_ext_ExtensionsManager|MockObject */
    private $extensionsManager;

    protected function setUp(): void
    {
        $this->extensionsManager = $this->createMock(common_ext_ExtensionsManager::class);

        $this->sut = new class () extends AbstractAdvancedSearchCheck {
            public function getDetails(): string
            {
                return 'details';
            }

            protected function doCheck(): common_report_Report
            {
                return Report::createInfo('report');
            }
        };
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    common_ext_ExtensionsManager::SERVICE_ID => $this->extensionsManager,
                ]
            )
        );
    }

    /**
     * @dataProvider isActiveProvider
     */
    public function testIsActive(array $installedExtensions, bool $expected): void
    {
        $this->extensionsManager
            ->expects($this->once())
            ->method('getInstalledExtensionsIds')
            ->willReturn($installedExtensions);

        $this->assertEquals($expected, $this->sut->isActive());
    }

    public function testGetType(): void
    {
        $this->assertEquals(CheckInterface::TYPE_SYSTEM, $this->sut->getType());
    }

    public function testGetCategory(): void
    {
        $this->assertEquals('Advanced Search', $this->sut->getCategory());
    }

    public function isActiveProvider(): array
    {
        return [
            'True' => [
                'installedExtensions' => ['taoAdvancedSearch'],
                'expected' => true,
            ],
            'False' => [
                'installedExtensions' => [],
                'expected' => false,
            ],
        ];
    }
}
