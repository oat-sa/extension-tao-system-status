<?php

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\StuckTasksCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

final class Version202109221117462719_taoSystemStatus extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $check = new StuckTasksCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->addCheck($check);
    }

    public function down(Schema $schema): void
    {
        $check = new StuckTasksCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->removeCheck($check);
    }
}
