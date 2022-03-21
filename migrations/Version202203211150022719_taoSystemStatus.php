<?php

declare(strict_types=1);

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\CertificateCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202203211150022719_taoSystemStatus extends AbstractMigration
{
    public function getDescription(): string
    {
        return sprintf('Remove check `%s` from the check list', CertificateCheck::class);
    }

    public function up(Schema $schema): void
    {
        $check = new CertificateCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->removeCheck($check);
    }

    public function down(Schema $schema): void
    {
        $check = new CertificateCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->addCheck($check);
    }
}
