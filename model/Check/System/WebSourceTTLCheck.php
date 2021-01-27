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
use oat\tao\model\websource\WebsourceManager;
use common_ext_ExtensionException;
use oat\taoSystemStatus\model\Check\AbstractCheck;


/**
 * Class WebSourceTTLCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class WebSourceTTLCheck extends AbstractCheck
{
    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $configDir = ROOT_PATH.'config/tao';
        $dirIterator = new \DirectoryIterator($configDir);
        $errorReport = null;
        foreach ($dirIterator as $config) {
            if (!$config->isDir()|| !$config->isDot()) {
                if (strpos($config->getFilename() , WebsourceManager::CONFIG_PREFIX) === 0) {
                    $ext = $this->getExtensionsManagerService()->getExtensionById('tao');
                    $configName = explode('.', $config->getFilename(), 2)[0];
                    $options = $ext->getConfig($configName);
                    if (isset($options['options'])) {
                        $ttl = $options['options']['ttl'] ?? 0;
                        if ($ttl === 0) {
                            $errorReport .= __('Web Source config %s has no TTL value.', $configName) . PHP_EOL;
                        }
                    }
                }
            }
        }
        if ($errorReport) {
            return new Report(Report::TYPE_WARNING, $errorReport);
        }
        return new Report(Report::TYPE_SUCCESS, __('Web Source is configured correctly.'));
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
        return __('Web sources configuration');
    }
}
