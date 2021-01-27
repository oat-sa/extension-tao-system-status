<?php

declare(strict_types=1);

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\AlarmNotificationCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202101211221292719_taoSystemStatus extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register AlarmNotificationCheck';
    }

    public function up(Schema $schema): void
    {
        $check = new AlarmNotificationCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->addCheck($check);
    }

    public function down(Schema $schema): void
    {
        $check = new AlarmNotificationCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->removeCheck($check);
    }
}
