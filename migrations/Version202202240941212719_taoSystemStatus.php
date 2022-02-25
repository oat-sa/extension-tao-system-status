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

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchStatusCheck;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchIndexationCheck;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchAvailabilityCheck;

final class Version202202240941212719_taoSystemStatus extends AbstractMigration
{
    private const CHECKS = [
        AdvancedSearchStatusCheck::class,
        AdvancedSearchAvailabilityCheck::class,
        AdvancedSearchIndexationCheck::class,
    ];

    public function getDescription(): string
    {
        return 'Add advanced search checks';
    }

    public function up(Schema $schema): void
    {
        $systemStatusService = $this->getSystemStatusService();

        foreach (self::CHECKS as $check) {
            $systemStatusService->addCheck(new $check());
        }
    }

    public function down(Schema $schema): void
    {
        $systemStatusService = $this->getSystemStatusService();

        foreach (self::CHECKS as $check) {
            $systemStatusService->removeCheck(new $check());
        }
    }

    private function getSystemStatusService(): SystemStatusService
    {
        return $this->getServiceLocator()->getContainer()->get(SystemStatusService::SERVICE_ID);
    }
}
