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
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\oatbox\log\loggerawaretrait;
use DateInterval;
use DateTime;
use Aws\ElastiCache\ElastiCacheClient;
use oat\taoSystemStatus\model\SystemCheckException;

/**
 * Class AwsRedisFreeSpaceCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class AwsRedisFreeSpaceCheck extends AbstractCheck
{
    use LoggerAwareTrait;

    const PARAM_PERIOD = 'period';
    const PARAM_DEFAULT_PERIOD = 300;

    /**
     * @param array $params
     * @return Report
     * @throws \Exception
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }

        $elastiCacheClient = $this->getElastiCacheClient();
        $redisHost = $this->getRedisHost();
        $stackId = $this->getStackId($redisHost);
        $cacheClusters = $elastiCacheClient->describeCacheClusters()->toArray();
        $clusterData = null;
        foreach ($cacheClusters['CacheClusters'] as $cacheCluster) {
            if (strpos($cacheCluster['CacheClusterId'], $stackId) === 0) {
                $clusterData = $cacheCluster;
                break;
            }
        }

        if ($clusterData === null) {
            throw new SystemCheckException('ElastiCache cluster not found');
        }

        $freeSpacePercentage = $this->getFreePercentage($clusterData['CacheClusterId']);

        if ($freeSpacePercentage < 30) {
            $report = new Report(Report::TYPE_ERROR, __('Redis storage has less than 30% of free space'));
        } elseif ($freeSpacePercentage < 50) {
            $report = new Report(Report::TYPE_WARNING, __('Redis storage has less than 50% of free space'));
        } else {
            $report = new Report(Report::TYPE_SUCCESS, __('Redis storage has %s%% of free space', round($freeSpacePercentage)));
        }

        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isAws() && $this->getRedisHost() !== null;
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
        return __('Environment health');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check free space on ElastiCache storage');
    }

    /**
     * @return ElastiCacheClient
     */
    private function getElastiCacheClient(): ElastiCacheClient
    {
        return new ElastiCacheClient($this->getServiceLocator()->get('generis/awsClient')->getOptions());
    }

    /**
     * @param string $redisHost
     * @return string
     * @throws SystemCheckException
     */
    private function getStackId(string $redisHost)
    {
        $hostParts = explode('.', $redisHost);
        if (!isset($hostParts[2])) {
            throw new SystemCheckException('Cannot get stack id by redis host');
        }
        return $hostParts[2];
    }

    /**
     * @return null|string
     */
    private function getRedisHost()
    {
        $persistences = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)
            ->getOption(PersistenceManager::OPTION_PERSISTENCES);

        $host = null;
        foreach ($persistences as $persistence) {
            if (isset($persistence['driver']) && $persistence['driver'] === 'phpredis') {
                $host = $persistence['host'];
            }
        }
        return $host;
    }

    /**
     * @param string $clusterId
     * @return float|int
     * @throws SystemCheckException
     */
    public function getFreePercentage(string $clusterId)
    {
        $period = $params[self::PARAM_PERIOD] ?? self::PARAM_DEFAULT_PERIOD;
        $interval = new DateInterval('PT' . $period . 'S');
        $since = (new DateTime())->sub($interval);
        $cloudWatchClient = $this->getServiceLocator()->get('generis/awsClient')->getCloudWatchClient();
        $result = $cloudWatchClient->getMetricData([
            'StartTime' => $since,
            'EndTime' => (new DateTime()),
            'MetricDataQueries' => [
                [
                    'Id' => 'free',
                    'MetricStat' => [
                        'Metric' => [
                            'Namespace' => 'AWS/ElastiCache',
                            'MetricName' => 'FreeableMemory',
                            'Dimensions' => [
                                [
                                    'Name' => 'CacheClusterId',
                                    'Value' => $clusterId
                                ]
                            ]
                        ],
                        'Period' => $period,
                        'Stat' => 'Average',
                    ]
                ],
                [
                    'Id' => 'used',
                    'MetricStat' => [
                        'Metric' => [
                            'Namespace' => 'AWS/ElastiCache',
                            'MetricName' => 'BytesUsedForCache',
                            'Dimensions' => [
                                [
                                    'Name' => 'CacheClusterId',
                                    'Value' => $clusterId
                                ]
                            ]
                        ],
                        'Period' => $period,
                        'Stat' => 'Average',
                    ]
                ]
            ]
        ]);

        $usedBytes = null;
        $freeBytes = null;
        foreach ($result->toArray()['MetricDataResults'] as $metric) {
            if ($metric['Id'] === 'used') {
                $usedBytes = $metric['Values'][0];
            }
            if ($metric['Id'] === 'free') {
                $freeBytes = $metric['Values'][0];
            }
        }
        if ($usedBytes === null || $freeBytes === null) {
            throw new SystemCheckException('Cannot get redis cluster metrics');
        }
        return $freeBytes / (($usedBytes + $freeBytes) / 100);
    }
}
