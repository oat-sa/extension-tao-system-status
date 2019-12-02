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

namespace oat\taoSystemStatus\model\CheckStorage;

use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\SystemStatusException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use ReflectionClass;

/**
 * @inheritdoc
 */
class RdsCheckStorage implements CheckStorageInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $persistenceId;

    const TABLE_NAME = 'system_checks';

    const COLUMN_ID = 'id';
    const COLUMN_CLASS = 'class';
    const COLUMN_TYPE = 'type';
    const COLUMN_PARAMS = 'params';

    const UNIQUE_ID_INDEX = 'idx_unique_system_checks_storage';

    /**
     * RdsCheckStorage constructor.
     * @param string $persistenceId
     */
    public function __construct(string $persistenceId)
    {
        $this->persistenceId = $persistenceId;
    }

    /**
     * @inheritdoc
     */
    public function addCheck(CheckInterface $check): bool
    {
        $data = [
            self::COLUMN_CLASS => get_class($check),
            self::COLUMN_ID => $check->getId(),
            self::COLUMN_TYPE => $check->getType(),
            self::COLUMN_PARAMS => json_encode($check->getParameters()),
        ];

        try {
            return $this->getPersistence()->insert(self::TABLE_NAME, $data) === 1;
        } catch (DBALException $e) {
            throw new SystemStatusException('Cannot add the Check to check storage: ' . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function removeCheck(CheckInterface $check): bool
    {
        $queryBuilder = $this->getPersistence()->getPlatForm()->getQueryBuilder();
        $queryBuilder->delete(self::TABLE_NAME);
        $queryBuilder->where(self::COLUMN_ID . ' = ?');
        $queryBuilder->setParameters([$check->getId()]);
        $stmt = $this->getPersistence()->query($queryBuilder->getSQL(), $queryBuilder->getParameters());
        return $stmt->execute();
    }

    /**
     * Get all checks by type
     *
     * @param string $type
     * @return array
     * @throws SystemStatusException if check class does not exist
     */
    public function getChecks(string $type): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select('*');
        $queryBuilder->where(self::COLUMN_TYPE . ' = ?');
        $queryBuilder->setParameters([$type]);
        $result = [];
        $stmt = $this->getPersistence()->query($queryBuilder->getSQL(), $queryBuilder->getParameters());
        $checksData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($checksData as $check) {
            try {
                $checkReflection = new ReflectionClass($check[self::COLUMN_CLASS]);
            } catch (\ReflectionException $e) {
                throw new SystemStatusException('Check class does not exist: ' . $check[self::COLUMN_CLASS]);
            }
            $result[] = $checkReflection->newInstanceArgs([
                json_decode($check[self::COLUMN_PARAMS], true),
            ]);
        }
        return $result;
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
            $table->addColumn(static::COLUMN_ID, 'string', ['length' => 255]);
            $table->addColumn(static::COLUMN_CLASS, 'text', ['notnull' => true]);
            $table->addColumn(static::COLUMN_PARAMS, 'text', ['notnull' => true]);
            $table->addColumn(static::COLUMN_TYPE, 'text', ['notnull' => true]);
            $table->addUniqueIndex([self::COLUMN_ID], self::UNIQUE_ID_INDEX);
        } catch(SchemaException $e) {
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
        return $this->getPersistence()->getPlatForm()->getQueryBuilder()->from(self::TABLE_NAME, 'r');
    }
}
