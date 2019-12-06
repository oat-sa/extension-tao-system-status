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
    const INSTANCE_ID_CALL_TIMEOUT = 10;
    const OPTION_STORAGE_CLASS = 'storage_class';
    const OPTION_STORAGE_PERSISTENCE = 'storage_persistence';
    const INSTANCE_ID_ENDPOINT = 'http://169.254.169.254/latest/meta-data/instance-id';

    /** @var CheckStorageInterface */
    protected $storage;

    /** @var string */
    protected $instanceId;

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
            $storageClass = $this->getOption(static::OPTION_STORAGE_CLASS);
            $this->storage = new $storageClass($persistenceId);
            $this->propagate($this->storage);
        }
        return $this->storage;
    }

    /**
     * @param Report $report
     * @return Report
     */
    protected function prepareReport(Report $report): Report
    {
        $report->setType(Report::TYPE_SUCCESS);
        $report->setMessage(__('All Systems Operational'));

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

    /**
     * Get instance type checks
     * @return array|CheckInterface[]
     */
    protected function getChecks(): array
    {
        $checks = $this->getCheckStorage()->getChecks($this->getChecksType());
        $result = [];
        foreach ($checks as $check) {
            $this->propagate($check);
            if ($check->isActive()) {
                $result[] = $check;
            }
        }
        return $result;
    }

    /**
     * Get type of checks which should be run by the service
     * @return string
     */
    abstract protected function getChecksType(): string;

    /**
     * @return string
     */
    public function getInstanceId(): string
    {
        if ($this->instanceId !== null) {
            return $this->instanceId;
        }

        //here we assume that ths is instance inside AWS stack.
        if ($this->getServiceLocator()->has('generis/awsClient')) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, self::INSTANCE_ID_ENDPOINT);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, self::INSTANCE_ID_CALL_TIMEOUT);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::INSTANCE_ID_CALL_TIMEOUT);
                $result = curl_exec($ch);
                curl_close($ch);
                if ($result) {
                    $this->instanceId = $result;
                } else {
                    $this->instanceId = $this->generateInstanceId();
                }
            } catch (\Throwable $e) {
                $this->instanceId = $this->generateInstanceId();
            }
        } elseif (false) { //here we need to check that we are on google environment
            //todo: call https://cloud.google.com/compute/docs/storing-retrieving-metadata
        } else {
            $this->instanceId = $this->generateInstanceId();
        }

        return $this->instanceId;
    }

    /**
     * @return bool|string
     */
    private function generateInstanceId(): string
    {
        $file = __DIR__ .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'instance-id';
        if (!file_exists($file)) {
            $this->instanceId = uniqid('i-' . time() . '_', true);
            file_put_contents($file, $this->instanceId);
        } else {
            $this->instanceId = file_get_contents($file);
        }
        return $this->instanceId;
    }
}
