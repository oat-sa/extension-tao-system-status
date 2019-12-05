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

namespace oat\taoSystemStatus\model\SystemStatusLog;

use common_report_Report as Report;
use oat\taoSystemStatus\model\Check\CheckInterface;

/**
 * Interface SystemStatusLogInterface
 *
 * Implementation of this interface supposed to store the log of check launches.
 *
 * @package oat\taoSystemStatus\model\SystemStatusLog
 */
interface SystemStatusLogStorageInterface
{
    const COLUMN_ID = 'id';
    const COLUMN_CHECK_ID = 'check_id';
    const COLUMN_INSTANCE_ID = 'instance_id';
    const COLUMN_REPORT = 'report';
    const COLUMN_CREATED_AT = 'created_at';

    /**
     * Log the result of checking
     *
     * @param Report $report
     * @param CheckInterface $check
     * @param string $instanceId
     * @return bool
     */
    public function log(CheckInterface $check, Report $report, string $instanceId = null);

    /**
     * Return latest logs grouped by check,instance
     *
     * @param \DateInterval $interval
     * @return mixed
     */
    public function getLatest(\DateInterval $interval);
}
