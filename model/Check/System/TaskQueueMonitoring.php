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

/**
 * Class TaskQueueMonitoring
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TaskQueueMonitoring extends AbstractCheck
{
    const REPORT_VALUE = 'report_value';
    const CATEGORY_ID = 'monitoring';
    const RENDERER = 'amountRenderer';

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }

        $amountOfTasks = $this->getAmountOfTasks();
        $report = new Report(Report::TYPE_INFO, $amountOfTasks);
        $report->setData([self::REPORT_VALUE => $amountOfTasks]);

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
        return __('Number of tasks in the task queue');
    }

    /**
     * @param Report $report
     * @return Report
     */
    protected function prepareReport(Report $report): Report
    {
        $report =  parent::prepareReport($report);
        $data = $report->getData();
        $data['renderer'] = self::RENDERER;
        $report->setData($data);
        return $report;
    }

    /**
     * @return int
     */
    private function getAmountOfTasks(): int
    {
        /** @var TaskLogInterface $taskQueueLog */
        $taskQueueLog = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);
        $filter = new TaskLogFilter();
        $filter->in(TaskLogBrokerInterface::COLUMN_STATUS, [TaskLogInterface::STATUS_ENQUEUED, TaskLogInterface::STATUS_RUNNING]);
        return $taskQueueLog->getBroker()->count($filter);
    }
}
