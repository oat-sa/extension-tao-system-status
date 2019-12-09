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
use oat\oatbox\log\loggerawaretrait;
use DateInterval;
use DateTime;
use Aws\ElastiCache\ElastiCacheClient;

/**
 * Class AwsRedisFreeSpaceCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class AwsRedisFreeSpaceCheck extends AbstractCheck
{
    use LoggerAwareTrait;

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

        $cloudWatchClient = $this->getServiceLocator()->get('generis/awsClient')->getCloudWatchClient();
        $elastiCacheClient = $this->getElastiCacheClient();

        var_dump($elastiCacheClient->describeCacheClusters());
        exit();

        $period = 300;
        $interval = new DateInterval('PT' . $period . 'S');
        $since = (new DateTime())->sub($interval);
        $result = $cloudWatchClient->getMetricData([
            'StartTime' => $since,
            'EndTime' => (new DateTime()),
            'MetricDataQueries' => [
                [
                    'Id' => 'm1',
                    'MetricStat' => [
                        'Metric' => [
                            'Namespace' => 'AWS/ElastiCache',
                            'MetricName' => 'FreeableMemory',
                            'Dimensions' => [
                                [
                                    'Name' => 'CacheClusterId',
                                    'Value' => 'usact03det-ectao-001'
                                ]
                            ]
                        ],
                        'Period' => $period,
                        'Stat' => 'Average',
                    ]
                ]
            ]
        ]);

        var_dump($result->get('MetricDataResults')[0]['Values'][0]);
        exit();

        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isAws();
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
        return __('Check free space on ElastiCache');
    }

    /**
     * @return ElastiCacheClient
     */
    private function getElastiCacheClient(): ElastiCacheClient
    {
        return new ElastiCacheClient($this->getServiceLocator()->get('generis/awsClient')->getOptions());
    }
}
