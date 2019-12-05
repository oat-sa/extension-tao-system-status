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
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\SystemStatusException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class SystemStatusLogService
 * @package oat\taoSystemStatus\model\SystemStatusLog
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class SystemStatusLogService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var SystemStatusLogStorageInterface */
    private $storage;

    const OPTION_STORAGE_CLASS = 'storage_class';
    const OPTION_STORAGE_PERSISTENCE = 'storage_persistence';

    /**
     * @param CheckInterface $check
     * @param Report $report
     */
    public function log(CheckInterface $check, Report $report)
    {
        $this->getStorage()->log($check, $report, $this->getInstanceId());
    }

    /**
     * @return array
     */
    public function getLatest(): array
    {
        return $this->getStorage()->getLatest();
    }

    /**
     * @return SystemStatusLogStorageInterface
     */
    private function getStorage(): SystemStatusLogStorageInterface
    {
        if (!$this->storage) {
            $persistenceId = $this->getOption(static::OPTION_STORAGE_PERSISTENCE);
            $storageClass = $this->getOption(static::OPTION_STORAGE_CLASS);
            $this->storage = new $storageClass($persistenceId);
            $this->propagate($this->storage);
        }
        return $this->storage;
    }

    /**
     * @return string
     */
    private function getInstanceId(): string
    {

    }
}
