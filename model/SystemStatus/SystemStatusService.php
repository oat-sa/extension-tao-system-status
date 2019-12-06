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
use oat\taoSystemStatus\model\SystemStatusLog\SystemStatusLogService;
use oat\taoSystemStatus\model\SystemStatusLog\SystemStatusLogStorageInterface;

/**
 * Class SystemStatusService
 *
 * Service supposed to run check with type of CheckInterface::TYPE_SYSTEM
 *
 * @package oat\taoSystemStatus\model\SystemStatus
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class SystemStatusService extends AbstractSystemStatusService
{
    const SERVICE_ID = 'taoSystemStatus/SystemStatusService';

    const OPTION_SUPPORT_PORTAL_LINK = 'support_portal_link';

    /**
     * @inheritdoc
     */
    public function check(): Report
    {
        $report = new Report(Report::TYPE_INFO);

        foreach ($this->getChecks() as $check) {
            try {
                $report->add($check());
            } catch (\Exception $e) {
                $this->logError(sprintf('Cannot run check `%s`; Error message: %s', $check->getId(), $e->getMessage()));
            }
        }
        foreach ($this->getInstanceCheckReports() as $instanceReport) {
            $report->add($instanceReport);
        }
        return $this->prepareReport($report);
    }

    /**
     * @return string|bool
     */
    public function getSupportPortalLink()
    {
        return $this->getOption(self::OPTION_SUPPORT_PORTAL_LINK);
    }
    /**
     * @return Report[]
     * @throws \common_exception_Error
     */
    protected function getInstanceCheckReports()
    {
        /** @var Report[] $result */
        $result = [];
        /** @var SystemStatusLogService $instanceStatusService */
        $instanceStatusService = $this->getServiceLocator()->get(SystemStatusLogService::SERVICE_ID);
        $instanceReports = $instanceStatusService->getLatest();
        $severityMap = [
            Report::TYPE_ERROR => 3,
            Report::TYPE_WARNING => 2,
            Report::TYPE_INFO => 1,
            Report::TYPE_SUCCESS => 0,
        ];
        foreach ($instanceReports as $instanceReport) {
            $report = Report::jsonUnserialize($instanceReport[SystemStatusLogStorageInterface::COLUMN_REPORT]);
            $checkId = $instanceReport[SystemStatusLogStorageInterface::COLUMN_CHECK_ID];
            if (!isset($result[$checkId])) {
                $result[$checkId] = $report;
                continue;
            }
            if ($severityMap[$result[$checkId]->getType()] < $severityMap[$report->getType()]) {
                $result[$checkId] = $report;
            }
        }
        return array_values($result);
    }

    /**
     * @return string
     */
    protected function getChecksType(): string
    {
        return CheckInterface::TYPE_SYSTEM;
    }
}
