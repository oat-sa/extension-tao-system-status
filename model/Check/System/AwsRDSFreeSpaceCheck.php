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

use Aws\Rds\RdsClient;
use common_report_Report as Report;
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\oatbox\log\loggerawaretrait;
use DateInterval;
use DateTime;
use oat\taoSystemStatus\model\SystemCheckException;
use oat\awsTools\AwsClient;
use oat\tao\helpers\Template;

/**
 * Class AwsRedisFreeSpaceCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class AwsRDSFreeSpaceCheck extends AbstractCheck
{
    use LoggerAwareTrait;

    const PARAM_PERIOD = 'period';
    const PARAM_DEFAULT_PERIOD = 300;

    const REPORT_VALUE = 'report_value';

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

        $rdsHost = $this->getRDSHost();
        $stackId = $this->getStackId($rdsHost);

        $dbInstances = $this->getRdsClient()->describeDBInstances()->toArray();

        $InstanceData = null;
        foreach ($dbInstances['DBInstances'] as $dbInstance) {
            if (strpos($dbInstance['DBInstanceIdentifier'], $stackId) === 0) {

                $InstanceData = $dbInstance;
                break;
            }
        }

        if ($InstanceData === null) {
            throw new SystemCheckException('RDS instance not found');
        }

        $freeSpacePercentage = $this->getFreePercentage($InstanceData);

        if ($freeSpacePercentage < 30) {
            $report = new Report(Report::TYPE_ERROR, round($freeSpacePercentage).'%');
        } elseif ($freeSpacePercentage < 50) {
            $report = new Report(Report::TYPE_WARNING, round($freeSpacePercentage).'%');
        } else {
            $report = new Report(Report::TYPE_SUCCESS, round($freeSpacePercentage).'%');
        }
        $report->setData([self::REPORT_VALUE => round($freeSpacePercentage)]);
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
     * @return RdsClient
     */
    private function getRdsClient(): RdsClient
    {
        return new RdsClient($this->getAwsClient()->getOptions());
    }

    /**
     * @param string $rdsHost
     * @return string
     * @throws SystemCheckException
     */
    private function getStackId(string $rdsHost) : string
    {
        $hostParts = explode('.', $rdsHost);
        if (!isset($hostParts[2])) {
            throw new SystemCheckException('Cannot get stack id by rds host');
        }
        return $hostParts[2];
    }

    /**
     * @return null|string
     */
    private function getRDSHost() : string
    {
        $persistences = $this->getPersistenceManager()
            ->getOption(PersistenceManager::OPTION_PERSISTENCES);

        $host = null;
        foreach ($persistences as $persistence) {
            if (isset($persistence['driver']) && $persistence['driver'] === 'dbal') {
                $host = $persistence['connection']['host'];
            }
        }
        return $host;
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
     * @return AwsClient
     */
    private function getAwsClient(): AwsClient
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get('generis/awsClient');
    }

    /**
     * @return PersistenceManager
     */
    private function getPersistenceManager() : PersistenceManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
    }

    /**
     * @param Report $report
     * @return string
     * @throws \common_Exception
     */
    public function renderReport(Report $report): string
    {
        $label = $report->getData()[self::PARAM_DETAILS];
        //to show used space instead of free space
        $val = 100-$report->getData()[self::REPORT_VALUE];
        $renderer = new \Renderer(Template::getTemplate('Reports/pieChart.tpl', 'taoSystemStatus'));
        $renderer->setData('label', $label);
        $renderer->setData('val', $val);
        $renderer->setData('type', $report->getType());
        return $renderer->render();
    }
}
