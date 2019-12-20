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
     * @param array $params
     * @return Report
     * @throws common_ext_ExtensionException
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkWebSourceTTL();
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
        return self::TYPE_SYSTEM;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('Configuration Values');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check Web Source config.');
    }

    /**
     * @return Report
     * @throws common_ext_ExtensionException
     */
    private function checkWebSourceTTL() : Report
    {
        $configDir = ROOT_PATH.'config/tao';
        $dirIterator = new \DirectoryIterator($configDir);
        $errorReport = null;
        foreach ($dirIterator as $config) {
            if (!$config->isDir()|| !$config->isDot()) {
                if(strpos($config->getFilename() , WebsourceManager::CONFIG_PREFIX) === 0) {
                    $ext = $this->getExtensionsManagerService()->getExtensionById('tao');
                    $configName = explode('.', $config->getFilename(), 2)[0];
                    $options = $ext->getConfig($configName);
                    if (isset($options['options'])) {
                        $ttl = $options['options']['ttl'] ?? 0;
                        if ($ttl > 0 && $ttl < 7200) {
                            $errorReport .= __('Web Source config %s has wrong configuration for ttl. Should be 7200. Current value is %s.', $configName, $ttl ?? 0) . PHP_EOL;
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
}
