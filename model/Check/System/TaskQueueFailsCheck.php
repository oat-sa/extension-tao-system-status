<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use oat\tao\model\taskQueue\TaskLog\CollectionInterface;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\helpers\Template;
use RecursiveIteratorIterator;
use common_Exception;
use common_exception_Error;

/**
 * Class TaskQueueFailsCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TaskQueueFailsCheck extends AbstractCheck
{

    const PARAM_LIMIT = 'limit';
    const PARAM_DEFAULT_LIMIT = 10;
    const TASK_REPORT_TIME = 'task_report_time';
    const TASK_LABEL = 'task_label';

    /**
     * @param array $params
     * @return Report
     * @throws common_Exception
     * @throws common_exception_Error
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }

        $limit = $params[self::PARAM_LIMIT] ?? self::PARAM_DEFAULT_LIMIT;
        $tasks = $this->getLastFailedTasks($limit);

        if ($tasks->isEmpty()) {
            return $this->prepareReport(new Report(Report::TYPE_SUCCESS, __('No failed tasks in the task queue log')));
        }

        $report = new Report(Report::TYPE_WARNING, __('Last %d failed tasks:', $tasks->count()));

        foreach ($tasks as $task) {
            $taskReport = $task->getReport();
            $data = $taskReport->getData();
            $data[self::TASK_REPORT_TIME] = \tao_helpers_Date::displayeDate($task->getCreatedAt());
            $data[self::TASK_LABEL] = $task->getLabel();

            $taskReportFormatted = new Report($taskReport->getType(), $taskReport->getMessage());
            $taskReportFormatted->setData($data);
            foreach ($this->getRecursiveReportIterator($taskReport) as $child) {
                $childReportFormatted = new Report($child->getType(), $child->getMessage());
                $childReportFormatted->setData($child->getData());
                $taskReportFormatted->add($childReportFormatted);
            }
            $report->add($taskReportFormatted);
        }

        return $this->prepareReport($report);
    }


    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->getServiceLocator()->has(TaskLogInterface::SERVICE_ID);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_SYSTEM;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('Monitoring / Statistics');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Last failed tasks in the task queue');
    }

    /**
     * @param Report $report
     * @return string
     * @throws common_Exception
     */
    public function renderReport(Report $report): string
    {
        $renderer = new \Renderer(Template::getTemplate('Reports/taskReport.tpl', 'taoSystemStatus'));
        $taskReports = [];
        /** @var Report $taskReport */
        foreach ($report->getChildren() as $taskReport) {
            $taskReports[] = [
                'task-report' => $taskReport,
                'task-report-flat' => $taskReport->getChildren()
            ];
        }
        $renderer->setData('reports', $taskReports);
        return $renderer->render();
    }

    /**
     * @param int $amount
     * @return CollectionInterface|EntityInterface[]
     */
    private function getLastFailedTasks(int $amount)
    {
        /** @var TaskLogInterface $taskQueueLog */
        $taskQueueLog = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
        $filter = new TaskLogFilter();
        $filter->eq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_FAILED);
        $filter->setLimit($amount);
        $filter->setSortBy(TaskLogBrokerInterface::COLUMN_CREATED_AT);
        $filter->setSortOrder('DESC');
        return $taskQueueLog->search($filter);
    }

    /**
     * @param $report
     * @return RecursiveIteratorIterator
     */
    private function getRecursiveReportIterator(Report $report) : RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            new \common_report_RecursiveReportIterator($report->getChildren()),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }
}
