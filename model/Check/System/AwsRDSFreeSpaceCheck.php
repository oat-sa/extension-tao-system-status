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

use oat\oatbox\reporting\Report;
use DateInterval;
use DateTime;
use oat\taoSystemStatus\model\Check\Traits\PieChartReportRenderer;
use oat\taoSystemStatus\model\SystemCheckException;

/**
 * Class AwsRedisFreeSpaceCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class AwsRDSFreeSpaceCheck extends AbstractAwsRDSCheck
{
    use PieChartReportRenderer;

    private const PARAM_PERIOD = 'period';

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $rdsHost = $this->getRDSHost();
        $stackId = $this->getStackId($rdsHost);

        $InstanceData = $this->getInstanceData($stackId);

        if ($InstanceData === null) {
            throw new SystemCheckException('RDS instance not found');
        }

        $freeSpacePercentage = $this->getFreePercentage($InstanceData);

        if ($freeSpacePercentage < 30) {
            $this->logError(__('Free space on RDS storage') . '< 30%');
            $report = new Report(Report::TYPE_ERROR, round($freeSpacePercentage).'%');
        } elseif ($freeSpacePercentage < 50) {
            $report = new Report(Report::TYPE_WARNING, round($freeSpacePercentage).'%');
        } else {
            $report = new Report(Report::TYPE_SUCCESS, round($freeSpacePercentage).'%');
        }
        $report->setData([self::PARAM_VALUE => round($freeSpacePercentage)]);

        return $report;
    }

    /**
     * @return bool
     * @throws SystemCheckException
     */
    public function isActive(): bool
    {
        $instanceData = $this->getInstanceData($this->getStackId($this->getRDSHost()));

        return $this->isAws() && $instanceData && array_key_exists('DBInstanceIdentifier', $instanceData);
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
        return __('Used space on RDS storage');
    }

    /**
     * @param array $instanceData
     * @return float|int
     * @throws SystemCheckException
     */
    public function getFreePercentage(array $instanceData)
    {
        $period = $params[self::PARAM_PERIOD] ?? self::PARAM_DEFAULT_PERIOD;
        $interval = new DateInterval('PT' . $period . 'S');
        $since = (new DateTime())->sub($interval);
        $cloudWatchClient = $this->getAwsClient()->getCloudWatchClient();
        $result = $cloudWatchClient->getMetricData([
            'StartTime' => $since,
            'EndTime' => (new DateTime()),
            'MetricDataQueries' => [
                [
                    'Id' => 'free',
                    'MetricStat' => [
                        'Metric' => [
                            'Namespace' => 'AWS/RDS',
                            'MetricName' => 'FreeStorageSpace',
                            'Dimensions' => [
                                [
                                    'Name' => 'DBInstanceIdentifier',
                                    'Value' => $instanceData['DBInstanceIdentifier']
                                ]
                            ]
                        ],
                        'Period' => $period,
                        'Stat' => 'Average',
                    ]
                ]
            ]
        ]);

        $freeGB = null;

        foreach ($result->toArray()['MetricDataResults'] as $metric) {
            if ($metric['Id'] === 'free') {
                $freeGB = $metric['Values'][0] / (1024*1024*1024);
            }
        }
        $allocatedStorage = $instanceData['AllocatedStorage'];

        if ($freeGB === null) {
            throw new SystemCheckException('Cannot get rds instance metrics');
        }
        return $freeGB / ($allocatedStorage / 100);
    }

    /**
     * @param string $stackId
     * @return array|null
     */
    protected function getInstanceData(string $stackId):? array
    {
        $dbInstances = $this->getRdsClient()->describeDBInstances()->toArray();

        if (!array_key_exists('DBInstances', $dbInstances)) {
            return null;
        }

        $InstanceData = null;
        foreach ($dbInstances['DBInstances'] as $dbInstance) {
            if (strpos($dbInstance['DBInstanceIdentifier'], $stackId) === 0) {
                $InstanceData = $dbInstance;
                break;
            }
        }

        return $InstanceData;
    }
}
