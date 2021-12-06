<?php

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\taoSystemStatus\model\Check\AbstractCheck;

class StuckTasksCheck extends AbstractCheck
{
    use LoggerAwareTrait;

    protected function doCheck(): Report
    {
        $message = '';
        $allStuckTasks = $this->getAllStuckTasks();

        if (empty($allStuckTasks)){
            $report = new Report(Report::TYPE_SUCCESS, __('There is no stuck tasks'));
        } else {
            foreach ($allStuckTasks as $stuckTask) {
                $task = [
                    'task_id' => $stuckTask['task_id'],
                    'status' => $stuckTask['status'],
                ];
                $message .= PHP_EOL . __('<b>' . $task['status'] . '</b> ' . $task['task_id']);
                $report = new Report(Report::TYPE_WARNING, __($message));
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

    public function isActive(): bool
    {
        return true;
    }

    private function getStuckTasksCheckService(): StuckTasksCheckService
    {
        return $this->getServiceLocator()->get(StuckTasksCheckService::SERVICE_ID);
    }

    private function getEnqueuedTasks()
    {
        $enqueuedMaxTime = $this->getStuckTasksCheckService()->getEnqueuedMaxTime();
        $maxEnqueued = time() - $enqueuedMaxTime * 3600;

        /** @var TaskLogInterface $taskQueueLog */
        $taskQueueLog = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
        $filter = (new TaskLogFilter())
            ->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_DEQUEUED])
            ->lte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $maxEnqueued);
        $tasks = $taskQueueLog->getBroker()->search($filter);

        foreach ($tasks as $task) {
            $enqueuedTasks[] = [
                'task_id' => $task->getId(),
                'status' => $task->getStatus(),
            ];
        }
        return $enqueuedTasks;
    }

    private function getRunningTasks()
    {
        $runningMaxTime = $this->getStuckTasksCheckService()->getRunningMaxTime();
        $maxRunning = time() - $runningMaxTime * 3600;

        /** @var TaskLogInterface $taskQueueLog */
        $taskQueueLog = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
        $filter = (new TaskLogFilter())
            ->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_RUNNING])
            ->lte(TaskLogBrokerInterface::COLUMN_UPDATED_AT, $maxRunning);
        $tasks = $taskQueueLog->getBroker()->search($filter);

        foreach ($tasks as $task) {
            $runningTasks[] = [
                'task_id' => $task->getId(),
                'status' => $task->getStatus(),
            ];
        }
        return $runningTasks;
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
