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

namespace oat\taoSystemStatus\model\Check\Instance;

use common_report_Report as Report;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class WriteConfigDataCheck
 * @package oat\taoSystemStatus\model\Check\Instance
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class WriteConfigDataCheck extends AbstractCheck
{
    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $configDir = ROOT_PATH.'config';
        $dataDir = ROOT_PATH.'data';

        if (!is_writable($configDir)) {
            return new Report(Report::TYPE_ERROR, __('TAO has no permissions to write into \'config\' folder'));
        }

        $dir = new \DirectoryIterator($configDir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                if (!is_writable($fileinfo->getPathname())) {
                    $message = __('TAO has no permissions to write into \'config/%s\' folder', $fileinfo->getFilename());
                    $this->logError($message);
                    return new Report(Report::TYPE_ERROR, $message);
                }
            }
        }

        if (!file_exists($dataDir) || !is_dir($dataDir)) {
            $message = __('\'data\' folder does not exist');
            $this->logError($message);
            return new Report(Report::TYPE_ERROR, $message);
        }
        if (!is_writable($dataDir)) {
            $message = __('TAO has no permissions to write into \'data\' folder');
            $this->logError($message);
            return new Report(Report::TYPE_ERROR, $message);
        }

        return new Report(Report::TYPE_SUCCESS, __('TAO has permissions to write into \'config\' and \'data\' folders'));
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return true;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_INSTANCE;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('Health/Readiness check');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Permissions to write into \'config\' and \'data\' folders');
    }
}
