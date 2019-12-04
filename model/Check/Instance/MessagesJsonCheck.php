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
use common_ext_ExtensionException;

/**
 * Class MessagesJsonCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class MessagesJsonCheck extends AbstractCheck
{

    const TAO_LOCALES_PREFIX = 'views/locales';
    const MESSAGES_JSON_NAME = 'messages.json';

    /**
     * @param array $params
     * @return Report
     * @throws common_ext_ExtensionException
     */
    public function __invoke($params = []): Report
    {
        $report = $this->checkMessagesJson();
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
        return __('Check if messages.json is exist');
    }

    /**
     * @return Report
     * @throws common_ext_ExtensionException
     */
    private function checkMessagesJson() : Report
    {
        $taoDir = $this->getExtensionsManagerService()->getExtensionById('tao')->getDir();
        $dir = new \DirectoryIterator($taoDir.self::TAO_LOCALES_PREFIX);
        $isDir = false;
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $isDir = true;
                if (!file_exists($fileinfo->getPathname().'/'.self::MESSAGES_JSON_NAME)) {
                    return new Report(Report::TYPE_WARNING, __('Missed messages.json for '.$fileinfo->getFilename()));
                }
            }
        }
        if (!$isDir) {
            return new Report(Report::TYPE_WARNING, __('Locales folder is empty'));
        }
        return new Report(Report::TYPE_SUCCESS, __('All messages.json exists'));

    }
}
