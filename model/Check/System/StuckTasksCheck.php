<?php

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\helpers\Template;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\taoSystemStatus\model\SystemStatus\StuckTasksCheckService;
use DateTimeImmutable;

class StuckTasksCheck extends AbstractCheck
{
    use LoggerAwareTrait;

    const OPTION_TASK_ID = 'task_id';
    const OPTION_LABEL = 'label';
    const OPTION_STATUS = 'status';
    const OPTION_UPDATED = 'updated_at';

    protected function doCheck(): Report
    {
        $allStuckTasks = $this->getAllStuckTasks();

        if (empty($allStuckTasks)){
            $report = new Report(Report::TYPE_SUCCESS, __('There is no stuck tasks'));
        } else {
            $report = new Report(Report::TYPE_WARNING);
            foreach ($allStuckTasks as $stuckTask) {
                $report->add(new Report(Report::TYPE_INFO, '', $stuckTask));
            }
        }
        return $report;
    }

    public function getType(): string
    {
        return self::TYPE_SYSTEM;
    }

    public function getCategory(): string
    {
        return __('Monitoring / Statistics');
    }

    public function getDetails(): string
    {
        return __('Stuck tasks');
    }

    public function renderReport(Report $report): string
    {
        $renderer = new \Renderer(Template::getTemplate('Reports/stuckTasksList.tpl', 'taoSystemStatus'));
        $taskReports = [];
        /** @var Report $taskReport */
        foreach ($report->getChildren() as $taskReport) {
            $taskReports[] = [
               'task-report' => $taskReport,
            ];
        }
        $renderer->setData('reports', $taskReports);
        return $renderer->render();
    }

    public function isActive(): bool
    {
        return true;
    }

    private function getTaskByStatusAndTime(DateTimeImmutable $time, array $status): array
    {
        $filter = (new TaskLogFilter())
            ->in(TaskLogBrokerInterface::COLUMN_STATUS, $status)
            ->lte(TaskLogBrokerInterface::COLUMN_UPDATED_AT, $time->format('Y-m-d H:i:s'))
            ->setLimit(10)
            ->setSortBy(TaskLogBrokerInterface::COLUMN_UPDATED_AT)
            ->setSortOrder('DESC');
        $tasks = $this->getTaskLogInterface()->getBroker()->search($filter);
        $runningTasks = [];

        foreach ($tasks as $task) {
            $runningTasks[] = $this->getTaskData($task);
        }

        return $runningTasks;
    }

    private function getTaskData(EntityInterface $task): array
    {
        return [
            self::OPTION_TASK_ID => $task->getId(),
            self::OPTION_LABEL => $task->getLabel(),
            self::OPTION_STATUS => $task->getStatus(),
            self::OPTION_UPDATED => \tao_helpers_Date::displayeDate($task->getUpdatedAt()),
        ];
    }

    private function getAgeTimeRunning(): DateTimeImmutable
    {
        $runningMaxTime = $this->getStuckTasksCheckService()->getRunningMaxTime();
        return $this->formatTime($runningMaxTime);
    }

    private function getAgeTimeEnqueued(): DateTimeImmutable
    {
        $enqueuedMaxTime = $this->getStuckTasksCheckService()->getEnqueuedMaxTime();
        return $this->formatTime($enqueuedMaxTime);
    }

    private function formatTime(float $time): DateTimeImmutable
    {
        $ageDateTime = new \DateTimeImmutable(
            sprintf('now -%s minutes', $time),
            new \DateTimeZone('UTC')
        );
        return $ageDateTime;
    }

    private function getAllStuckTasks(): array
    {
        $enqueuedTasks = $this->getTaskByStatusAndTime($this->getAgeTimeEnqueued(), [TaskLogInterface::STATUS_ENQUEUED]);
        $runningTasks = $this->getTaskByStatusAndTime($this->getAgeTimeRunning(), [TaskLogInterface::STATUS_RUNNING]);
        return array_merge($enqueuedTasks, $runningTasks);
    }

    private function getStuckTasksCheckService(): StuckTasksCheckService
    {
        return $this->getServiceLocator()->get(StuckTasksCheckService::SERVICE_ID);
    }

    private function getTaskLogInterface(): TaskLogInterface
    {
        return  $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
    }
}
