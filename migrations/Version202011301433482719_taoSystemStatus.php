<?php

declare(strict_types=1);

namespace oat\taoSystemStatus\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoScheduler\model\scheduler\SchedulerServiceInterface;
use oat\taoTaskQueue\scripts\tools\AddTaskToQueue;
use oat\taoSystemStatus\scripts\tools\CheckDegradations;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202011301433482719_taoSystemStatus extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Schedule a job to check degradations';
    }

    public function up(Schema $schema): void
    {
        /** @var SchedulerServiceInterface $schedulerService */
        $schedulerService = $this->getServiceLocator()->get(SchedulerServiceInterface::SERVICE_ID);
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $schedulerService->attach(
            '*/30 * * * *',
            $date,
            CheckDegradations::class,
            ['--persistence', 'default_kv']
        );
    }

    public function down(Schema $schema): void
    {
        /** @var SchedulerServiceInterface $schedulerService */
        $schedulerService = $this->getServiceLocator()->get(SchedulerServiceInterface::SERVICE_ID);
        foreach ($schedulerService->getJobs() as $job) {
            if ($job->getCallable() === CheckDegradations::class) {
                $schedulerService->detach($job->getRRule(), $job->getStartTime(), $job->getCallable());
            }
        }
    }
}
