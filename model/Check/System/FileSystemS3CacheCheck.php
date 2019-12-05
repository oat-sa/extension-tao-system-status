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
use oat\oatbox\filesystem\FileSystemService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class FileSystemS3CacheCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class FileSystemS3CacheCheck extends AbstractCheck
{
    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        $report = $this->checkFileSystemConfig();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $fileSystem = $this->getFileSystemService();
        return class_exists('oat\awsTools\AwsFileSystemService')
            && get_class($fileSystem) === 'oat\awsTools\AwsFileSystemService';
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
        return __('System configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check that filesystem adapters configured to S3 have cache enabled');
    }

    /**
     * @return Report
     */
    private function checkFileSystemConfig() : Report
    {
        $adapters = $this->getFileSystemService()->getOption('adapters');
        foreach ($adapters as $name => $adapter) {
            if ($this->isAdapterForCheck($name)) {
                $options = array_pop($adapter['options']);
                if (!isset($options['cache'])) {
                    return new Report(Report::TYPE_WARNING, __('File System adapter %s should be cacheable.', $name));
                }
            }
        }
        return new Report(Report::TYPE_SUCCESS, __('Filesystem adapters correctly configured.'));
    }

    /**
     * @return FileSystemService
     */
    private function getFileSystemService() : FileSystemService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
    }

    /**
     * @param $adapter
     * @return bool
     */
    private function isAdapterForCheck($adapter) : bool
    {
        $adapters = [
            'public',
            'private',
            'qtiItemPci',
            'qtiItemImsPci',
            'portableElementStorage'
        ];

        return in_array($adapter, $adapters, true);
    }
}
