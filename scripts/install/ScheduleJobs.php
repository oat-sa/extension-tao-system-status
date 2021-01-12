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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoSystemStatus\scripts\install;

use oat\oatbox\extension\AbstractAction;
use common_report_Report as Report;
use oat\taoScheduler\model\scheduler\SchedulerServiceInterface;
use oat\taoSystemStatus\scripts\tools\CheckDegradations;

/**
 * Class ScheduleJobs
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @package oat\taoSystemStatus\scripts\install
 */
class ScheduleJobs extends AbstractAction
{
    /**
     * @param $params
     * @return Report
     * @throws \Exception
     */
    public function __invoke($params)
    {
        /** @var SchedulerServiceInterface $schedulerService */
        $schedulerService = $this->getServiceLocator()->get(SchedulerServiceInterface::SERVICE_ID);
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $schedulerService->attach(
            '*/30 * * * *',
            $date,
            CheckDegradations::class,
            ['--persistence', 'default_kv']
        );

        return new Report(Report::TYPE_SUCCESS, __('System status jobs successfully scheduled.'));
    }
}
