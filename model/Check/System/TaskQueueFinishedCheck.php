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
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\helpers\Template;
use common_Exception;
use Exception;
use common_exception_Error;

/**
 * Class TaskQueueFinishedCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class TaskQueueFinishedCheck extends AbstractCheck
{
    const OPTIONS_INTERVAL = [
        'P1D' => 'PT1H',
        'P1M' => 'P1D',
        'P1W' => 'P1D'
    ];

    /**
     * @param array $params
     * @return Report
     * @throws common_exception_Error
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $statistics = $this->getTasksStatistics();

        $report = new Report(Report::TYPE_SUCCESS, __('Task Queue statistics:'));
        $childReport = new Report(Report::TYPE_SUCCESS, __('Completed and archived tasks:'));
        $childReport->setData($statistics);
        $report->add($childReport);

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
        return __('Show last finished tasks in the task queue');
    }

    /**
     * @param Report $report
     * @return string
     * @throws common_Exception
     */
    public function renderReport(Report $report): string
    {
        $result = parent::renderReport($report);
        /** @var Report $taskReport */
        foreach ($report->getChildren() as $taskReport) {
            $renderer = new \Renderer(Template::getTemplate('Reports/taskStatisticsReport.tpl', 'taoSystemStatus'));
            $renderer->setData('task-report', $taskReport);
            $renderer->setData('task-statistics', json_encode($taskReport->getData()));
            $result .= $renderer->render();
        }
        return $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getTasksStatistics() : array
    {
        $taskQueueLog = $this->getTaskLogService();

        $result = [];
        foreach (self::OPTIONS_INTERVAL as $name => $interval) {
            $intervalObj = new \DateInterval($interval);

            $timeKeys = \tao_helpers_Date::getTimeKeys(
                new \DateInterval($interval),
                new \DateTime('now', new \DateTimeZone('UTC'))
            );
            if ($name === 'P1W') {
                $timeKeys = \tao_helpers_Date::getTimeKeys(
                    new \DateInterval($interval),
                    new \DateTime('now', new \DateTimeZone('UTC'))
                    , 7
                );
            }
            foreach ($timeKeys as $timeKey) {
                $to = clone($timeKey);
                $from = clone($to);
                $from->sub($intervalObj);
                $filter = new TaskLogFilter();
                $filter->eq(TaskLogBrokerInterface::COLUMN_STATUS, TaskLogInterface::STATUS_COMPLETED);
                $filter->gte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $from->format('Y-m-d H:i:s'));
                $filter->lte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $to->format('Y-m-d H:i:s'));

                $tasks = $taskQueueLog->search($filter);
                $times = [];
                foreach ($tasks as $task) {
                    $executionTime = $task->getUpdatedAt()->getTimestamp() - $task->getCreatedAt()->getTimestamp();
                    $times[] = $executionTime;
                }
                $result[$name]['time'][] = $from->format('Y-m-d H:i:s');
                $result[$name]['average'][] = empty($times) ? 0: array_sum($times) / count($times);
                $result[$name]['amount'][] = count($tasks);
            }
        }
        return $result;
    }

    /**
     * @return TaskLogInterface
     */
    private function getTaskLogService() : TaskLogInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
    }
}
