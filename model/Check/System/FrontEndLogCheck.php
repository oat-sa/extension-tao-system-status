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
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class FrontEndLogCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class FrontEndLogCheck extends AbstractCheck
{

    /**
     * @param array $params
     * @return Report
     * @throws \common_ext_ExtensionException
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $config = $this->getExtensionsManagerService()->getExtensionById('tao')->getConfig('client_lib_config_registry');
        $enabled = isset($config['core/logger']['loggers']['core/logger/http']) &&
            in_array($config['core/logger']['loggers']['core/logger/http']['level'], ['error', 'warn']);

        if ($enabled) {
            $report = new Report(Report::TYPE_SUCCESS, __('Front end log enabled'));
        } else {
            $report = new Report(Report::TYPE_ERROR, __('Front end log disabled'));
        }

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
        return __('System configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check if frontend log correctly configured');
    }
}
