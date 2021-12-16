<?php

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\helpers\Template;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\taoSystemStatus\model\SystemStatus\StuckTasksCheckService;

class StuckTasksCheck extends AbstractCheck
{
    use LoggerAwareTrait;

    const OPTION_TASK_ID = 'task_id';
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
                $task = [
                    self::OPTION_TASK_ID => $stuckTask[self::OPTION_TASK_ID],
                    self::OPTION_STATUS => $stuckTask[self::OPTION_STATUS],
                    self::OPTION_UPDATED => $stuckTask[self::OPTION_UPDATED],
                ];
                $report->add(new Report(Report::TYPE_INFO, '', $task));
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

    private function getStuckTasksCheckService(): StuckTasksCheckService
    {
        return $this->getServiceLocator()->get(StuckTasksCheckService::SERVICE_ID);
    }

    private function getTaskLogInterface(): TaskLogInterface
    {
        return  $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
    }

    private function getEnqueuedTasks()
    {
        $filter = (new TaskLogFilter())
            ->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_ENQUEUED])
            ->lte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $this->getAgeTimeEnqueued()->format('Y-m-d H:i:s'))
            ->setLimit(10)
            ->setSortBy(TaskLogBrokerInterface::COLUMN_CREATED_AT)
            ->setSortOrder('DESC');
        $tasks = $this->getTaskLogInterface()->getBroker()->search($filter);

        foreach ($tasks as $task) {
            $enqueuedTasks[] = [
                self::OPTION_TASK_ID => $task->getId(),
                self::OPTION_STATUS => $task->getStatus(),
                self::OPTION_UPDATED => \tao_helpers_Date::displayeDate($task->getUpdatedAt()),
            ];
        }
        return $enqueuedTasks;
    }

    private function getRunningTasks()
    {
        $filter = (new TaskLogFilter())
            ->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_RUNNING])
            ->lte(TaskLogBrokerInterface::COLUMN_UPDATED_AT, $this->getAgeTimeRunning()->format('Y-m-d H:i:s'))
            ->setLimit(10)
            ->setSortBy(TaskLogBrokerInterface::COLUMN_UPDATED_AT)
            ->setSortOrder('DESC');
        $tasks = $this->getTaskLogInterface()->getBroker()->search($filter);

        foreach ($tasks as $task) {
            $runningTasks[] = [
                self::OPTION_TASK_ID => $task->getId(),
                self::OPTION_STATUS => $task->getStatus(),
                self::OPTION_UPDATED => \tao_helpers_Date::displayeDate($task->getUpdatedAt()),
            ];
        }
        return $runningTasks;
    }

    private function getAgeTimeRunning()
    {
        $runningMaxTime = $this->getStuckTasksCheckService()->getRunningMaxTime();

        $ageDateTime = new \DateTimeImmutable(
            sprintf('now -%s seconds', $runningMaxTime),
            new \DateTimeZone('UTC')
        );
        return $ageDateTime;
    }

    private function getAgeTimeEnqueued()
    {
        $enqueuedMaxTime = $this->getStuckTasksCheckService()->getEnqueuedMaxTime();

        $ageDateTime = new \DateTimeImmutable(
            sprintf('now -%s seconds', $enqueuedMaxTime),
            new \DateTimeZone('UTC')
        );
        return $ageDateTime;
    }

    private function getAllStuckTasks(): array
    {
        $enqueuedTasks = $this->getEnqueuedTasks();
        $runningTasks = $this->getRunningTasks();
        $allStuckTasks = [];

        if (is_array($enqueuedTasks) && is_array($runningTasks)) {
            $allStuckTasks = array_merge($enqueuedTasks, $runningTasks);
        } elseif (is_array($enqueuedTasks) && !is_array($runningTasks)) {
            $allStuckTasks = $enqueuedTasks;
        } elseif (!is_array($enqueuedTasks) && is_array($runningTasks)) {
            $allStuckTasks = $runningTasks;
        }
        return $allStuckTasks;
    }
}
