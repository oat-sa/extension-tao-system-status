<?php

declare(strict_types=1);

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\FileSystemS3CachePathCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202010150815402719_taoSystemStatus extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register FileSystemS3CachePathCheck';
    }

    public function up(Schema $schema): void
    {
        $check = new FileSystemS3CachePathCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->addCheck($check);
    }

    public function down(Schema $schema): void
    {
        $check = new FileSystemS3CachePathCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->removeCheck($check);
    }
}
