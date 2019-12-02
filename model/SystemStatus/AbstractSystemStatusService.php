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

namespace oat\taoSystemStatus\model\SystemStatus;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\taoSystemStatus\model\CheckStorage\CheckStorageInterface;
use oat\taoSystemStatus\model\Check\CheckInterface;
use common_report_Report as Report;

/**
 * Class AbstractSystemStatusService
 * @package oat\taoSystemStatus\model\SystemStatus
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractSystemStatusService extends ConfigurableService implements SystemStatusServiceInterface
{

    const OPTION_STORAGE_CLASS = 'storage_class';
    const OPTION_STORAGE_PERSISTENCE = 'storage_persistence';

    /** @var CheckStorageInterface */
    protected $storage;

    /**
     * @inheritdoc
     */
    public function addCheck(CheckInterface $check): bool
    {
        return $this->getCheckStorage()->addCheck($check);
    }

    /**
     *  @inheritdoc
     */
    public function removeCheck(CheckInterface $check): bool
    {
        return $this->getCheckStorage()->removeCheck($check);
    }

    /**
     * @return CheckStorageInterface
     */
    protected function getCheckStorage(): CheckStorageInterface
    {
        if (!$this->storage) {
            $persistenceId = $this->getOption(static::OPTION_STORAGE_PERSISTENCE);
            $persistence = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)->getPersistenceById($persistenceId);
            $storageClass = $this->getOption(static::OPTION_STORAGE_CLASS);
            $this->storage = new $storageClass($persistence);
        }
        return $this->storage;
    }

    /**
     * @param Report $report
     * @return Report
     */
    protected function prepareReport(Report $report): Report
    {
        if ($report->contains(Report::TYPE_WARNING)) {
            $report->setType(Report::TYPE_WARNING);
            $report->setMessage(__('Partially Degraded Service'));
        }

        if ($report->contains(Report::TYPE_ERROR)) {
            $report->setType(Report::TYPE_ERROR);
            $report->setMessage(__('We\'re noticing an increased rate of errors'));
        }

        return $report;
    }
}
