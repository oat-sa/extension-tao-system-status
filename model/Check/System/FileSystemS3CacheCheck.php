<?php

declare(strict_types=1);

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

    const CACHED_FILESYSTEMS = [
        'public',
        'private',
        'qtiItemPci',
        'qtiItemImsPci',
        'portableElementStorage'
    ];

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Cache status on S3 file system adapters');
    }

    /**
     * @return Report
     */
    private function checkFileSystemConfig() : Report
    {
        $adaptersWithoutCache = [];
        foreach (self::CACHED_FILESYSTEMS as $fsId) {
            $adapter = $this->getFlysystemAdapterConfig($fsId);
            $options = array_pop($adapter['options']);
            if (!isset($options['cache'])) {
                $adaptersWithoutCache[] = $fsId;
            }
        }

        if (!empty($adaptersWithoutCache)) {
            return new Report(
                Report::TYPE_WARNING,
                __('Cache is disabled for File System adapters: %s', implode(', ', $adaptersWithoutCache))
            );
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
     * Get configuration of filesystem adapter
     * @param $id
     * @return mixed
     */
    private function getFlysystemAdapterConfig(string $id): array
    {
        $fsService = $this->getFileSystemService();
        $dirs = $fsService->hasOption(FileSystemService::OPTION_DIRECTORIES)
            ? $fsService->getOption(FileSystemService::OPTION_DIRECTORIES)
            : [];

        if (!isset($dirs[$id])) {
            $adapterId = $id;
        } elseif (is_array($dirs[$id])) {
            $adapterId = $dirs[$id]['adapter'];
        } else {
            $adapterId = $dirs[$id];
        }

        $fsConfig = $fsService->getOption(FileSystemService::OPTION_ADAPTERS);
        $adapterConfig = $fsConfig[$adapterId];
        // alias?
        while (is_string($adapterConfig)) {
            $adapterConfig = $fsConfig[$adapterConfig];
        }
        return $adapterConfig;
    }
}
