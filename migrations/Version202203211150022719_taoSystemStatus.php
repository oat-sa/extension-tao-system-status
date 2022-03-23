<?php

declare(strict_types=1);

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoSystemStatus\model\Check\System\CertificateCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

final class Version202203211150022719_taoSystemStatus extends AbstractMigration
{
    public function getDescription(): string
    {
        return sprintf('Remove check `%s` from the check list', CertificateCheck::class);
    }

    /**
     * @throws \oat\taoSystemStatus\model\SystemStatusException
     */
    public function up(Schema $schema): void
    {
        $this->getSystemStatusService()->removeCheck(new CertificateCheck([]));
    }

    /**
     * @throws \oat\taoSystemStatus\model\SystemStatusException
     */
    public function down(Schema $schema): void
    {
        $this->getSystemStatusService()->addCheck(new CertificateCheck([]));
    }

    private function getSystemStatusService(): SystemStatusService
    {
        return $this->getServiceLocator()->getContainer()->get(SystemStatusService::SERVICE_ID);
    }
}
