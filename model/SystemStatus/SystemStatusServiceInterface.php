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

namespace oat\taoSystemStatus\model\SystemStatus;

use common_report_Report as Report;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\SystemStatusException;

/**
 * Interface SystemCheckInterface
 *
 * Implementations of this interface will run all the registered checks and return report.
 *
 * @package oat\taoSystemStatus\model\SystemStatus
 */
interface SystemStatusServiceInterface
{
    /**
     * Run all registered checks and return report
     * @return Report
     */
    public function check(): Report;

    /**
     * @param CheckInterface $check
     * @return bool
     * @throws SystemStatusException check with given id already exists
     */
    public function addCheck(CheckInterface $check): bool;

    /**
     * @param string $id
     * @return CheckInterface
     */
    public function getCheck(string $id): CheckInterface;

    /**
     * @param CheckInterface $check
     * @return bool
     * @throws SystemStatusException check with given id does not exist
     */
    public function removeCheck(CheckInterface $check): bool;

    /**
     * Get server instance id
     * @return string
     */
    public function getInstanceId(): string;
}
