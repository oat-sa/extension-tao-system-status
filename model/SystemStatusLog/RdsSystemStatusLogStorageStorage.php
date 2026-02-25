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

namespace oat\taoSystemStatus\model\SystemStatusLog;

use common_report_Report as Report;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\SchemaException;
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\SystemStatusException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Rds implementation of SystemStatusLogStorageInterface
 *
 */
class RdsSystemStatusLogStorageStorage implements SystemStatusLogStorageInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    private $persistenceId;

    const TABLE_NAME = 'system_status_log';

    const CHECK_ID_INDEX = 'idx_system_status_log_check_id';
    const INSTANCE_ID_INDEX = 'idx_system_status_log_instance_id';

    /**
     * RdsSystemStatusLogStorageStorage constructor.
     * @param string $persistenceId
     */
    public function __construct(string $persistenceId)
    {
        $this->persistenceId = $persistenceId;
    }

    /**
     * @inheritDoc
     * @throws SystemStatusException
     */
    public function log(CheckInterface $check, Report $report, string $instanceId = null)
    {
        $data = [
            self::COLUMN_CHECK_ID    => $check->getId(),
            self::COLUMN_INSTANCE_ID => $instanceId,
            self::COLUMN_REPORT      => json_encode($report),
            self::COLUMN_CREATED_AT  => $this->getPersistence()->getPlatForm()->getNowExpression(),
        ];

        try {
            return $this->getPersistence()->insert(self::TABLE_NAME, $data) === 1;
        } catch (DBALException $e) {
            throw new SystemStatusException('Cannot log check '.$check->getId().' : ' . $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function getLatest(\DateInterval $interval)
    {
        $dbNow = $this->getPersistence()->getPlatForm()->getNowExpression();
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $dbNow);
        $date = $date->sub($interval);
        $conditionSql = $this->getQueryBuilder()
            ->select('max('.self::COLUMN_ID.')')
            ->where(self::COLUMN_CREATED_AT . ' > :date')
            ->groupBy([
                self::COLUMN_CHECK_ID,
                self::COLUMN_INSTANCE_ID
            ])
            ->getSQL();
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select(['*']);
        $queryBuilder->where(self::COLUMN_ID . ' IN (' . $conditionSql . ')');
        $queryBuilder->setParameter('date', $date->format('Y-m-d H:i:s'));
        $result = $this->getPersistence()->query($queryBuilder->getSQL(), $queryBuilder->getParameters());
        return $result->fetchAllAssociative();
    }


    /**
     * Install or prepare persistence
     *
     * @param $persistence \common_persistence_Persistence
     * @return boolean
     */
    public function install(\common_persistence_Persistence $persistence): bool
    {
        /** @var \common_persistence_sql_dbal_SchemaManager $schemaManager */
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            if ($schema->hasTable(self::TABLE_NAME)) {
                $table = $schema->getTable(self::TABLE_NAME);
            } else {
                $table = $schema->createTable(self::TABLE_NAME);
            }
            $table->addOption('engine', 'InnoDB');
            $table->addColumn(static::COLUMN_ID, 'integer', ['autoincrement' => true]);
            $table->addColumn(static::COLUMN_CHECK_ID, 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn(static::COLUMN_INSTANCE_ID, 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn(static::COLUMN_REPORT, 'text', ['notnull' => false]);
            $table->addColumn(static::COLUMN_CREATED_AT, 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

            $table->setPrimaryKey([static::COLUMN_ID]);
            $table->addIndex([static::COLUMN_CHECK_ID], static::CHECK_ID_INDEX);
            $table->addIndex([static::COLUMN_INSTANCE_ID], static::INSTANCE_ID_INDEX);
        } catch (SchemaException $e) {
            \common_Logger::w($e->getMessage());
            return false;
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
        return true;
    }

    /**
     * @return \common_persistence_SqlPersistence
     * @throws
     */
    private function getPersistence(): \common_persistence_SqlPersistence
    {
        $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
        return $persistenceManager->getPersistenceById($this->persistenceId);
    }

    /**
     * @return QueryBuilder
     * @throws
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->getPersistence()->getPlatForm()->getQueryBuilder()->from(self::TABLE_NAME);
    }
}
