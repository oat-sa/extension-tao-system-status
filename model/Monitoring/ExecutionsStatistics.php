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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Monitoring;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringService;
use oat\taoProctoring\model\monitorCache\implementation\MonitoringStorage;
use oat\taoSystemStatus\model\SystemStatusException;
use DateTime;
use DatePeriod;

/**
 * Class ExecutionsStatistics
 *
 * @package oat\taoSystemStatus\model\Monitoring
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ExecutionsStatistics extends ConfigurableService
{
    public const SERVICE_ID = 'taoSystemStatus/ExecutionsStatistics';

    /**
     * @param DatePeriod $period
     * @return array|mixed[]
     * @throws SystemStatusException
     */
    public function getStartedExecutionsData(DatePeriod $period)
    {
        $persistence = $this->getPersistence();

        $qb = $persistence->getPlatForm()->getQueryBuilder();
        $qb->select('COUNT(' . MonitoringStorage::COLUMN_DELIVERY_EXECUTION_ID . ')')
            ->from(MonitoringStorage::TABLE_NAME);

        $params = [];
        /** @var DateTime $previousDate */
        $previousDate = null;
        $queries = [];

        foreach ($period as $key => $date) {
            if ($key === 0) {
                $previousDate = $date;
                continue;
            }
            $timeExpr = '\'' . $date->format('Y-m-d H:i:s') . '\' as time';
            $countExpr = 'COUNT(' . MonitoringStorage::COLUMN_DELIVERY_EXECUTION_ID . ') as count';
            $qb->select($timeExpr . ', ' . $countExpr)
                ->where(
                    MonitoringStorage::COLUMN_START_TIME . ' >= ? AND ' . MonitoringStorage::COLUMN_START_TIME . ' < ?'
                );
            $params[] = (string) $previousDate->getTimestamp();
            $params[] = (string) $date->getTimestamp();

            $queries[] = $qb->getSQL();
            $previousDate = $date;
        }
        $sql = implode(' UNION ALL ', $queries);

        /** @var \Doctrine\DBAL\Result $result */
        $result = $persistence->getDriver()->query($sql, $params);
        return $result->fetchAllAssociative();
    }

    /**
     * @param DatePeriod $period
     * @return array|mixed[]
     * @throws SystemStatusException
     */
    public function getFinishedExecutionsData(DatePeriod $period)
    {
        $persistence = $this->getPersistence();

        $qb = $persistence->getPlatForm()->getQueryBuilder();
        $qb->select('COUNT(' . MonitoringStorage::COLUMN_DELIVERY_EXECUTION_ID . ')')
            ->from(MonitoringStorage::TABLE_NAME);

        $params = [];
        /** @var DateTime $previousDate */
        $previousDate = null;
        $queries = [];

        foreach ($period as $key => $date) {
            if ($key === 0) {
                $previousDate = $date;
                continue;
            }
            $timeExpr = '\'' . $date->format('Y-m-d H:i:s') . '\' as time';
            $countExpr = 'COUNT(' . MonitoringStorage::COLUMN_DELIVERY_EXECUTION_ID . ') as count';
            $qb->select($timeExpr . ', ' . $countExpr)
                ->where(
                    MonitoringStorage::COLUMN_END_TIME . ' >= ? AND ' . MonitoringStorage::COLUMN_END_TIME . ' < ?'
                );
            $params[] = (string) $previousDate->getTimestamp();
            $params[] = (string) $date->getTimestamp();

            $queries[] = $qb->getSQL();
            $previousDate = $date;
        }
        $sql = implode(' UNION ALL ', $queries);

        /** @var \Doctrine\DBAL\Result $result */
        $result = $persistence->getDriver()->query($sql, $params);
        return $result->fetchAllAssociative();
    }

    /**
     * @return \common_persistence_SqlPersistence
     * @throws SystemStatusException
     */
    private function getPersistence()
    {
        /** @var MonitoringStorage $deliveryMonitoringService */
        $deliveryMonitoringService = $this->getServiceLocator()->get(DeliveryMonitoringService::SERVICE_ID);
        if (!$deliveryMonitoringService instanceof MonitoringStorage) {
            throw new SystemStatusException('Only RDS implementation of DeliveryMonitoringService supported');
        }
        $persistenceId = $deliveryMonitoringService->getOption(MonitoringStorage::OPTION_PERSISTENCE);
        /** @var \common_persistence_SqlPersistence $persistence */
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($persistenceId);
    }
}
