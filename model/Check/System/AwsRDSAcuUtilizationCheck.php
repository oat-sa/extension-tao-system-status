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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Check\System;

use DateInterval;
use DateTime;
use oat\oatbox\reporting\Report;
use oat\taoSystemStatus\model\Check\Traits\PieChartReportRenderer;
use oat\taoSystemStatus\model\SystemCheckException;

/**
 * Class AwsRDSAcuUtilizationCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Makar Sichevoi, <makar.sichevoy@taotesting.com>
 */
class AwsRDSAcuUtilizationCheck extends AbstractAwsRDSCheck
{
    use PieChartReportRenderer;

    private const PARAM_PERIOD = 'period';
    private const ID_ACU_UTILIZATION = "acuutilization";
    private const NAMESPACE = 'AWS/RDS';
    private const METRIC_NAME = 'ACUUtilization';

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $instanceData = $this->getInstanceData();

        if ($instanceData === null) {
            throw new SystemCheckException('RDS cluster instance not found');
        }

        $utilizationPercentage = $this->getAcuUtilization($instanceData);

        if ($utilizationPercentage > 80) {
            $this->logError(__('ACU Utilization on RDS storage') . '> 80%');
            $report = new Report(Report::TYPE_ERROR, round($utilizationPercentage) . '%');
        } elseif ($utilizationPercentage > 50) {
            $report = new Report(Report::TYPE_WARNING, round($utilizationPercentage) . '%');
        } else {
            $report = new Report(Report::TYPE_SUCCESS, round($utilizationPercentage) . '%');
        }

        $report->setData([self::PARAM_VALUE => round($utilizationPercentage)]);

        return $report;
    }

    /**
     * @return bool
     * @throws SystemCheckException
     */
    public function isActive(): bool
    {
        if (!$this->isAws()) {
            return false;
        }

        $instanceData = $this->getInstanceData();

        return $instanceData && array_key_exists('DBClusterIdentifier', $instanceData);
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
        return __('ACU Utilization on RDS storage');
    }

    /**
     * @param array $instanceData
     * @return float|int
     * @throws SystemCheckException
     */
    public function getAcuUtilization(array $instanceData)
    {
        $period = $params[self::PARAM_PERIOD] ?? self::PARAM_DEFAULT_PERIOD;
        $interval = new DateInterval('PT' . $period . 'S');
        $since = (new DateTime())->sub($interval);
        $cloudWatchClient = $this->getAwsClient()->getCloudWatchClient();
        $result = $cloudWatchClient->getMetricData([
            'MetricDataQueries' => [
                [
                    'Id' => self::ID_ACU_UTILIZATION,
                    'MetricStat' => [
                        'Metric' => [
                            'Namespace' => self::NAMESPACE,
                            'MetricName' => self::METRIC_NAME,
                            'Dimensions' => [
                                [
                                    'Name' => 'DBClusterIdentifier',
                                    'Value' => $instanceData['DBClusterIdentifier']
                                ]
                            ]
                        ],
                        'Period' => $period,
                        'Stat' => 'Average',
                        'Unit' => 'Percent'
                    ]
                ],
            ],
            'StartTime' => $since,
            'EndTime' => (new DateTime()),
            'ScanBy' => 'TimestampDescending'
        ]);

        $utilization = null;

        foreach ($result->toArray()['MetricDataResults'] as $metric) {
            if ($metric['Id'] === self::ID_ACU_UTILIZATION) {
                $utilization = reset($metric['Values']);
            }
        }

        if ($utilization === null) {
            throw new SystemCheckException('Cannot get rds instance metrics');
        }

        return $utilization;
    }

    /**
     * @param string $stackId
     * @return null|array
     */
    protected function getInstanceData(): ?array
    {
        $rdsHost = $this->getRDSHost();
        $stackId = $this->getStackId($rdsHost);

        $dbClusterInstances = $this->getRdsClient()->describeDBClusters()->toArray();

        if (!array_key_exists('DBClusters', $dbClusterInstances)) {
            return null;
        }

        $instanceData = null;
        foreach ($dbClusterInstances['DBClusters'] as $dbInstance) {
            if (strpos($dbInstance['DBClusterIdentifier'], $stackId) === 0) {
                $instanceData = $dbInstance;
                break;
            }
        }

        return $instanceData;
    }
}
