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

/**
 * Class TaskQueueFinishedCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class TaskQueueFinishedCheck extends AbstractCheck
{
    const OPTIONS_INTERVAL = [
        'P1D' => 'PT10M',
        'P1W' => 'PT1H',
        'P1M' => 'PT4H'
    ];

    private const OPTIONAL_FILTER_FIELDS = [
        TaskLogBrokerInterface::COLUMN_PARAMETERS,
        TaskLogBrokerInterface::COLUMN_LABEL,
        TaskLogBrokerInterface::COLUMN_OWNER,
    ];

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $statistics = $this->getTasksStatistics();
//        print_r(json_encode($statistics, JSON_PRETTY_PRINT));die;

        $report = new Report(Report::TYPE_SUCCESS, __('Task Queue statistics:'));
        $report->setData($statistics);
        return $report;
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
        return __('Statistics of processed tasks');
    }

    /**
     * @param Report $report
     * @return string
     * @throws common_Exception
     */
    public function renderReport(Report $report): string
    {
        /** @var Report $taskReport */
        $renderer = new \Renderer(Template::getTemplate('Reports/taskStatisticsReport.tpl', 'taoSystemStatus'));
        $renderer->setData('task-report', $report);
        $renderer->setData('task-statistics', json_encode($report->getData()));
        return $renderer->render();
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
            $period = new \DateInterval($name);

            $points = round($this->getIntervalSeconds($period) / $this->getIntervalSeconds($intervalObj));

            $timeKeys = \tao_helpers_Date::getTimeKeys(
                new \DateInterval($interval),
                new \DateTime('now', new \DateTimeZone('UTC')),
                $points
            );

            foreach ($timeKeys as $timeKey) {
                $to = clone($timeKey);
                $from = clone($to);
                $from->sub($intervalObj);

//                $filter = new TaskLogFilter();
//                $filter->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_COMPLETED, TaskLogInterface::STATUS_ARCHIVED]);
//                $filter->gte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $from->format('Y-m-d H:i:s'));
//                $filter->lte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $to->format('Y-m-d H:i:s'));
//
//                foreach (self::OPTIONAL_FILTER_FIELDS as $fld) {
//                    $filter->deselect($fld);
//                }

                $tasks = $taskQueueLog->getMonitoringTaskqueueStats($from, $to);

                $times = [];
                foreach ($tasks as $task) {
                    $executionTime = $task['updatedAt']->getTimestamp() - $task['createdAt']->getTimestamp();
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
     * @param \DateInterval $interval
     * @return int
     * @throws Exception
     */
    private function getIntervalSeconds(\DateInterval $interval)
    {
        $reference = new \DateTimeImmutable;
        $endTime = $reference->add($interval);

        return $endTime->getTimestamp() - $reference->getTimestamp();
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
