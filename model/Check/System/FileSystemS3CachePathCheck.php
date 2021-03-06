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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class FileSystemS3CachePathCheck
 *
 * It is possible to upload a PHP file using the MediaManager extension.
 * It is important to note that this is the expected behavior of the application as all kind of files can be uploaded.
 *
 * Those files must be outside of directory available from web
 *
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <aleh@taotesting.com>
 */
class FileSystemS3CachePathCheck extends AbstractCheck
{
    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $adaptersWithCacheInsideTaoRoot = [];
        $notValidCachePaths = [];
        $taoRootDir = $this->getTaoRootDir();
        $adapters = $this->getFileSystemService()->getOption(FileSystemService::OPTION_ADAPTERS);
        foreach ($adapters as $adapterId => $adapterConfig) {
            if (!isset($adapterConfig['options'])) {
                continue;
            }
            $options = array_pop($adapterConfig['options']);
            if (!isset($options['cache']) || !is_string($options['cache'])) {
                continue;
            }
            $cachePath = realpath($options['cache']);
            if ($cachePath === false) {
                $notValidCachePaths[] = $options['cache'];
                continue;
            }
            if (strpos($cachePath, $taoRootDir) === 0) {
                $adaptersWithCacheInsideTaoRoot[] = $adapterId;
            }
        }

        if (!empty($notValidCachePaths)) {
            return new Report(
                Report::TYPE_WARNING,
                __('Cache path is not valid: %s', implode(', ', $notValidCachePaths))
            );
        }

        if (!empty($adaptersWithCacheInsideTaoRoot)) {
            return new Report(
                Report::TYPE_WARNING,
                __('Cache directory is inside tao root directory. Filesystem adapters: %s', implode(', ', $adaptersWithCacheInsideTaoRoot))
            );
        }

        return new Report(Report::TYPE_SUCCESS, __('Cache directories of filesystem adapters correctly configured.'));
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
        return __('Cache directory path on S3 file system adapters');
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
     * @return string
     */
    private function getTaoRootDir(): string
    {
        return realpath(rtrim(ROOT_PATH, '\\/').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
    }
}
