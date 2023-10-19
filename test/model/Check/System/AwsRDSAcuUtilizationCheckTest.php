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

declare(strict_types=1);

namespace oat\taoSystemStatus\test\model\Check\System;

use Aws\Result;
use oat\awsTools\AwsClient;
use oat\generis\persistence\PersistenceManager;
use oat\generis\test\PersistenceManagerMockTrait;
use oat\oatbox\service\ServiceManager;
use oat\taoSystemStatus\model\Check\System\AwsRDSAcuUtilizationCheck;
use oat\taoSystemStatus\model\SystemCheckException;
use oat\taoSystemStatus\test\model\Check\System\Stub\CloudwatchClientStub;
use oat\taoSystemStatus\test\model\Check\System\Stub\RdsClientStub;
use PHPUnit\Framework\TestCase;

class AwsRDSAcuUtilizationCheckTest extends TestCase
{
    use PersistenceManagerMockTrait;

    private AwsRDSAcuUtilizationCheck $awsRDSAcuUtilizationCheckPartialMock;
    private PersistenceManager $persistenceManagerMock;
    private ServiceManager $serviceLocatorMock;

    protected function setUp(): void
    {
        $this->awsRDSAcuUtilizationCheckPartialMock = $this->createPartialMock(
            AwsRDSAcuUtilizationCheck::class,
            ['getRdsClient']
        );

        $this->serviceLocatorMock = $this->createMock(ServiceManager::class);
        $this->persistenceManagerMock = $this->createMock(PersistenceManager::class);
    }

    public function testIsActiveOnAwsCluster(): void
    {
        $this->serviceLocatorMock->expects(self::any())
            ->method('has')
            ->with('generis/awsClient')
            ->willReturn(true);

        $this->serviceLocatorMock->expects(self::any())
            ->method('get')
            ->with(PersistenceManager::SERVICE_ID)
            ->willReturn($this->persistenceManagerMock);

        $rdsClientMock = $this->createMock(RdsClientStub::class);
        $awsResult = new Result([
            'DBClusters' => [
                ['DBClusterIdentifier' => 'test2-dbcluster-idintifier'],
                ['DBClusterIdentifier' => 'test-dbcluster-idintifier'],
            ]
        ]);

        $rdsClientMock
            ->expects(self::once())
            ->method('describeDBClusters')
            ->willReturn($awsResult);

        $this->awsRDSAcuUtilizationCheckPartialMock
            ->expects(self::once())
            ->method('getRdsClient')
            ->willReturn($rdsClientMock);

        $this->awsRDSAcuUtilizationCheckPartialMock->setServiceLocator($this->serviceLocatorMock);

        $this->persistenceManagerMock
            ->expects($this->once())
            ->method('getOption')
            ->willReturn([['driver' => 'dbal', 'connection' => ['host' => 'testconfig.rds.test-dbcluster-idintifier']]]
            );

        $isActive = $this->awsRDSAcuUtilizationCheckPartialMock->isActive();

        self::assertTrue($isActive);
    }

    public function testIsActiveOnAwsDbInstance(): void
    {
        $this->serviceLocatorMock->expects(self::any())
            ->method('has')
            ->with('generis/awsClient')
            ->willReturn(true);

        $this->serviceLocatorMock->expects(self::any())
            ->method('get')
            ->with(PersistenceManager::SERVICE_ID)
            ->willReturn($this->persistenceManagerMock);

        $rdsClientMock = $this->createMock(RdsClientStub::class);
        $awsResult = new Result([
            'DBInstances' => [
                ['DBInstanceIdentifier' => 'test2-dbinstance-idintifier'],
                ['DBInstanceIdentifier' => 'test1-dbinstance-idintifier'],
            ]
        ]);

        $rdsClientMock
            ->expects(self::once())
            ->method('describeDBClusters')
            ->willReturn($awsResult);

        $this->awsRDSAcuUtilizationCheckPartialMock
            ->expects(self::once())
            ->method('getRdsClient')
            ->willReturn($rdsClientMock);

        $this->awsRDSAcuUtilizationCheckPartialMock->setServiceLocator($this->serviceLocatorMock);

        $this->persistenceManagerMock
            ->expects($this->once())
            ->method('getOption')
            ->willReturn([['driver' => 'dbal', 'connection' => ['host' => 'testconfig.rds.test-dbinstance-idintifier']]]
            );

        $isActive = $this->awsRDSAcuUtilizationCheckPartialMock->isActive();

        self::assertFalse($isActive);
    }

    public function checkResults(): array
    {
        return [
            'Ok' => [1.0, [
                'type' => 'success',
                'message' => '1%',
                'value' => 1
            ]],
            'Warning' => [55.0, [
                'type' => 'warning',
                'message' => '55%',
                'value' => 55
            ]],
            'Error' => [90.0, [
                'type' => 'error',
                'message' => '90%',
                'value' => 90
            ]]
        ];
    }

    /**
     * @param float $utilizationValue
     * @param array $results
     * @return void
     * @dataProvider checkResults
     */
    public function testOnDoCheck(float $utilizationValue, array $results): void
    {
        $awsClientMock = $this->createMock(AwsClient::class);
        $cloudWatchClientMock = $this->createMock(CloudwatchClientStub::class);
        $this->awsRDSAcuUtilizationCheckPartialMock->setServiceLocator($this->serviceLocatorMock);

        $this->serviceLocatorMock->expects(self::any())
            ->method('has')
            ->with('generis/awsClient')
            ->willReturn(true);

        $this->serviceLocatorMock->expects(self::any())
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->persistenceManagerMock,
                $this->persistenceManagerMock,
                $awsClientMock
            );

        $rdsClientMock = $this->createMock(RdsClientStub::class);
        $awsResult = new Result([
            'DBClusters' => [
                ['DBClusterIdentifier' => 'test2-dbcluster-idintifier'],
                ['DBClusterIdentifier' => 'test1-dbcluster-idintifier'],
            ]
        ]);

        $rdsClientMock
            ->expects(self::exactly(2))
            ->method('describeDBClusters')
            ->willReturn($awsResult);

        $this->awsRDSAcuUtilizationCheckPartialMock
            ->expects(self::exactly(2))
            ->method('getRdsClient')
            ->willReturn($rdsClientMock);

        $this->persistenceManagerMock
            ->expects(self::exactly(2))
            ->method('getOption')
            ->willReturn([['driver' => 'dbal', 'connection' => ['host' => 'testconfig.rds.test1-dbcluster-idintifier']]]
            );

        $awsClientMock
            ->expects(self::once())
            ->method('getCloudWatchClient')
            ->willReturn($cloudWatchClientMock);
        $cloudWatchClientMock
            ->expects(self::once())
            ->method('getMetricData')
            ->willReturn(
                new Result(
                    [
                        "MetricDataResults" => [
                            [
                                "Id" => "acuutilization",
                                "Label" => "ACUUtilization",
                                "Timestamps" => [
                                    "2023-10-13T08:45:00+00:00",
                                    "2023-10-13T08:40:00+00:00"
                                ],
                                "Values" => [
                                    $utilizationValue,
                                    30.0
                                ],
                                "StatusCode" => "Complete"
                            ]
                        ],
                        "Messages" => []
                    ]
                )
            );

        $utilizationResult = $this->awsRDSAcuUtilizationCheckPartialMock->__invoke();

        $result = $utilizationResult->toArray();
        self::assertEquals($results['type'], $result['type']);
        self::assertEquals($results['message'], $result['message']);
        self::assertEquals($results['value'], $result['data']['value']);
        self::assertEquals('Monitoring / Statistics', $result['data']['category']);
        self::assertEquals('ACU Utilization on RDS storage', $result['data']['details']);
    }

    public function testOnDoCheckError(): void
    {
        $this->expectException(SystemCheckException::class);
        $this->expectExceptionMessage('Cannot get rds instance metrics');

        $awsClientMock = $this->createMock(AwsClient::class);
        $cloudWatchClientMock = $this->createMock(CloudwatchClientStub::class);
        $this->awsRDSAcuUtilizationCheckPartialMock->setServiceLocator($this->serviceLocatorMock);

        $this->serviceLocatorMock->expects(self::any())
            ->method('has')
            ->with('generis/awsClient')
            ->willReturn(true);

        $this->serviceLocatorMock->expects(self::any())
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->persistenceManagerMock,
                $this->persistenceManagerMock,
                $awsClientMock
            );

        $rdsClientMock = $this->createMock(RdsClientStub::class);
        $awsResult = new Result([
            'DBClusters' => [
                ['DBClusterIdentifier' => 'test2-dbcluster-idintifier'],
                ['DBClusterIdentifier' => 'test1-dbcluster-idintifier'],
            ]
        ]);

        $rdsClientMock
            ->expects(self::exactly(2))
            ->method('describeDBClusters')
            ->willReturn($awsResult);

        $this->awsRDSAcuUtilizationCheckPartialMock
            ->expects(self::exactly(2))
            ->method('getRdsClient')
            ->willReturn($rdsClientMock);

        $this->persistenceManagerMock
            ->expects(self::exactly(2))
            ->method('getOption')
            ->willReturn([['driver' => 'dbal', 'connection' => ['host' => 'testconfig.rds.test1-dbcluster-idintifier']]]
            );

        $awsClientMock
            ->expects(self::once())
            ->method('getCloudWatchClient')
            ->willReturn($cloudWatchClientMock);
        $cloudWatchClientMock
            ->expects(self::once())
            ->method('getMetricData')
            ->willReturn(
                new Result(
                    [
                        "MetricDataResults" => [
                            [
                                "Id" => "free",
                                "Label" => "ACUUtilization",
                                "Timestamps" => [
                                    "2023-10-13T08:45:00+00:00",
                                    "2023-10-13T08:40:00+00:00"
                                ],
                                "Values" => [
                                    55.0,
                                    30.0
                                ],
                                "StatusCode" => "Complete"
                            ]
                        ],
                        "Messages" => []
                    ]
                )
            );

        $this->awsRDSAcuUtilizationCheckPartialMock->__invoke();
    }
}
