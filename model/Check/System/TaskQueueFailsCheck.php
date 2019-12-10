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
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;
use oat\tao\helpers\Template;

/**
 * Class TaskQueueFailsCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TaskQueueFailsCheck extends AbstractCheck
{

    const PARAM_LIMIT = 'limit';
    const PARAM_DEFAULT_LIMIT = 10;

    /**
     * @param array $params
     * @return Report
     * @throws \common_exception_Error
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }

        $limit = $params[self::PARAM_LIMIT] ?? self::PARAM_DEFAULT_LIMIT;
        $tasks = $this->getLaskFailedTasks($limit);

        if (empty($tasks)) {
            return new Report(Report::TYPE_SUCCESS, __('No failed tasks in the task queue log'));
        } else {
            $report = new Report(Report::TYPE_WARNING, __('Last %n failed tasks:', $limit));
        }

        foreach ($tasks as $task) {
            $report->add($task->getReport());
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
        return __('Task Queue Status');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Show last failed tasks in the task queue');
    }

    /**
     * @param Report $report
     * @param null $template
     * @return string
     * @throws \common_Exception
     */
    public function renderReport(Report $report): string
    {
        $result = parent::renderReport($report);
        foreach ($report->getChildren() as $taskReport) {
            $flat = [];
            foreach ($this->getRecursiveReportIterator($taskReport) as $child) {
                $flat[] = $child;
            }
            $renderer = new \Renderer(Template::getTemplate('Reports/taskReport.tpl', 'taoSystemStatus'));
            $renderer->setData('task-report', $taskReport);
            $renderer->setData('reports', $flat);
            $result .= $renderer->render();
        }

        return $result;
    }

    /**
     * @param int $amount
     * @return EntityInterface[]
     */
    private function getLaskFailedTasks(int $amount)
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
     * @return \RecursiveIteratorIterator
     */
    private function getRecursiveReportIterator(Report $report)
    {
        return new \RecursiveIteratorIterator(
            new \common_report_RecursiveReportIterator($report->getChildren()),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }
}
