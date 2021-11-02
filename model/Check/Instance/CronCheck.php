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

namespace oat\taoSystemStatus\model\Check\Instance;

use common_report_Report as Report;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\tao\model\taskQueue\QueueDispatcherInterface;

/**
 * Class CronCheck
 * @package oat\taoSystemStatus\model\Check\Instance
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class CronCheck extends AbstractCheck
{
    const PARAM_CRON_PATH = 'cron_path';
    const DEFAULT_CRON_PATH = '/etc/cron.d';

    private $cronPath;

    /** @var string regular expression to check if string contains CRON rule */
    private $cronRegEx = '/(\*|(\d|1\d|2\d|3\d|4\d|5\d)|\*\/(\d|1\d|2\d|3\d|4\d|5\d)) (\*|(\d|1\d|2[0-3])|\*\/(\d|1\d|2[0-3])) (\*|([1-9]|1\d|2\d|3[0-1])|\*\/([1-9]|1\d|2\d|3[0-1])) (\*|([1-9]|1[0-2])|\*\/([1-9]|1[0-2])) (\*|([0-6])|\*\/([0-6]))/';

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $this->cronPath = $params[self::PARAM_CRON_PATH] ?? self::DEFAULT_CRON_PATH;
        $report = new Report(Report::TYPE_INFO);
        $report->add($this->checkTaskQueueCron());

        if ($this->getExtensionsManagerService()->isInstalled('taoScheduler')) {
            $report->add($this->checkSchedulerCron());
        }

        if (count($report->getSuccesses()) === count($report->getChildren())) {
            $report->setType(Report::TYPE_SUCCESS);
        } elseif(count($report->getErrors()) > 0)  {
            $report->setType(Report::TYPE_ERROR);
            $this->logError('CRON job not configured properly.');
        } else {
            $report->setType(Report::TYPE_WARNING);
        }

        return $report;
    }

    /**
     * @param Report $report
     * @return Report
     */
    protected function prepareReport(Report $report): Report
    {
        $msg = '';
        /** @var Report $childReport */
        foreach ($report->getIterator() as $childReport) {
            $msg .= $childReport->getMessage() . PHP_EOL;
        }
        $report->setMessage($msg);
        return parent::prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->isWorker();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_INSTANCE;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('Health/Readiness check');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('CRON jobs configuration');
    }

    /**
     * @return Report
     */
    private function checkSchedulerCron(): Report
    {
        $cronJobs = $this->getCronJobs();
        $cronJobExists = false;

        foreach ($cronJobs as $cronJob) {
            if (strpos($cronJob, 'JobRunner')) {
                $cronJobExists = true;
            }
        }

        if ($cronJobExists) {
            return new Report(Report::TYPE_SUCCESS, __('CRON job for Scheduler Job Runner correctly configured'));
        }
        return new Report(Report::TYPE_ERROR, __('CRON job for Scheduler Job Runner is missed'));
    }


    /**
     * @return Report
     */
    private function checkTaskQueueCron(): Report
    {
        $cronJobs = $this->getCronJobs();
        $oneJobForAllQueues = false;
        $cronQueueNames = [];

        foreach ($cronJobs as $cronJob) {
            if (strpos($cronJob, 'RunWorker')) {
                preg_match('/--queue\s*?=\s*?(\S+)/', $cronJob, $queueName);
                if (!isset($queueName[1])) {
                    $oneJobForAllQueues = true;
                } else {
                    $cronQueueNames[] = $queueName[1];
                }
            }
        }

        /** @var QueueDispatcherInterface $queue */
        $queueService = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);

        if ($oneJobForAllQueues) {
            return new Report(Report::TYPE_SUCCESS, __('%d queues configured. One worker is used for all queues', count($queueService->getQueues())));
        }

        $queueNames = [];

        foreach ($queueService->getQueues() as $queue) {
            if ($queue->isSync()) {
                continue;
            }
            $queueNames[] = $queue->getName();
        }

        $queuesWithoutCron = array_diff($queueNames, $cronQueueNames);

        if (!empty($queuesWithoutCron)) {
            return new Report(Report::TYPE_ERROR, __('CRON jobs are not configured for the following queues: %s', implode(', ', $queuesWithoutCron)));
        }

        return new Report(Report::TYPE_SUCCESS, __('CRON jobs for all %d queues correctly configured', count($queueService->getQueues())));
    }

    /**
     * @return array
     */
    private function getCronJobs(): array
    {
        $cronDirIterator = new \DirectoryIterator($this->getCronJobsPath());

        $result = [];

        foreach ($cronDirIterator as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }
            $cronFile = $fileInfo->openFile('r');
            while (!$cronFile->eof()) {
                $line = trim($cronFile->fgets());
                if ((strpos($line, '#') === false || strpos($line, '#') > 0) && preg_match($this->cronRegEx, $line)) {
                    $result[] = $line;
                }
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getCronJobsPath(): string
    {
        return $this->cronPath ?? self::DEFAULT_CRON_PATH;
    }
}
