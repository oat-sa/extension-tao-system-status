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
 * Class TaoUpdateCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TaoUpdateCheck extends AbstractCheck
{

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $extManager = $this->getExtensionsManagerService();
        $notUpdated = [];
        foreach ($this->getExtensionsManagerService()->getInstalledExtensions() as $extension) {
            if ($extManager->getInstalledVersion($extension->getId()) !== $extension->getVersion()) {
                $notUpdated[] = $extension->getId();
            }
        }
        if (empty($notUpdated)) {
            $report = new Report(Report::TYPE_SUCCESS, __('All extensions are up to date'));
        } else {
            $report = new Report(Report::TYPE_WARNING, __('The following extensions require update: ') . implode(', ', $notUpdated));
        }
        return $report;
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
        return __('Health/Readiness check');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Extension updates');
    }
}
