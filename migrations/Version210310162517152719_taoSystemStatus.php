<?php

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\StuckTasksCheckService;

final class Version210310162517152719_taoSystemStatus extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $service = new StuckTasksCheckService([
            StuckTasksCheckService::RUNNING_MAX_TIME => 2,
            StuckTasksCheckService::ENQUEUED_MAX_TIME => 24,
        ]);

        $this->getServiceLocator()->register(StuckTasksCheckService::SERVICE_ID, $service);
    }
}
