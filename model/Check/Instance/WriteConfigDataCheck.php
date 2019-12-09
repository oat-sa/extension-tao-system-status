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
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class WriteConfigDataCheck extends AbstractCheck
{
    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkConfigAndDataFolders();
        return $this->prepareReport($report);
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
        return __('System configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check if TAO has permissions to write into \'config\' and \'data\' folders');
    }

    /**
     * @return Report
     */
    private function checkConfigAndDataFolders() : Report
    {
        $configDir = ROOT_PATH.'config';
        $dataDir = ROOT_PATH.'data';

        if (!is_writable($configDir)) {
            return new Report(Report::TYPE_ERROR, __('TAO has not permissions to write into \'config\' folder'));
        }

        $dir = new \DirectoryIterator($configDir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                if (!is_writable($fileinfo->getPathname())) {
                    return new Report(Report::TYPE_ERROR, __('TAO has not permissions to write into \'config/%s\' folder', $fileinfo->getFilename()));
                }
            }
        }

        if (!file_exists($dataDir)) {
            return new Report(Report::TYPE_ERROR, __('\'data\' folder is not exists'));
        }
        if (!is_writable($dataDir)) {
            return new Report(Report::TYPE_ERROR, __('TAO has not permissions to write into \'data\' folder'));
        }


        return new Report(Report::TYPE_SUCCESS, __('TAO has permissions to write into \'config\' and \'data\' folders'));

    }
}
