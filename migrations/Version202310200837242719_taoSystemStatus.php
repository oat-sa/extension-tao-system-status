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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\AwsRDSAcuUtilizationCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

final class Version202310200837242719_taoSystemStatus extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add aws ACUUtilization checks';
    }

    public function up(Schema $schema): void
    {
        $this->getSystemStatusService()->addCheck(new AwsRDSAcuUtilizationCheck([]));
    }

    public function down(Schema $schema): void
    {
        $this->getSystemStatusService()->removeCheck(new AwsRDSAcuUtilizationCheck([]));
    }

    private function getSystemStatusService(): SystemStatusService
    {
        return $this->getServiceLocator()->getContainer()->get(SystemStatusService::SERVICE_ID);
    }
}
