<?php

declare(strict_types=1);
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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Report;

use common_report_Report as Report;
use oat\taoSystemStatus\model\Check\CheckInterface;

/**
 * Class ReportComparator
 *
 * Comparator is used to get difference between 2 reports and find degradations or restorations of system status
 *
 * @package oat\taoSystemStatus\model\Report
 */
class ReportComparator
{
    /**
     * @var Report
     */
    private $oldReport;

    /**
     * @var Report
     */
    private $newReport;

    const REPORT_WEIGHT_MAP = [
        Report::TYPE_SUCCESS => 0,
        Report::TYPE_INFO => 1,
        Report::TYPE_WARNING => 2,
        Report::TYPE_ERROR => 3,
    ];

    /**
     * ReportComparator constructor.
     * @param Report $oldReport
     * @param Report $newReport
     */
    public function __construct(Report $oldReport, Report $newReport)
    {
        $this->oldReport = $oldReport;
        $this->newReport = $newReport;
    }

    /**
     * @return Report
     */
    public function getDegradations(): Report
    {
        $report = $this->compare(function ($trend) {
            return $trend < 0;
        });

        if ($report->hasChildren()) {
            $report->setType(Report::TYPE_WARNING);
            $report->setMessage('Service degradations found');
        }

        return $report;
    }

    public function getRestorations(): Report
    {
        $report = $this->compare(function ($trend) {
            return $trend > 0;
        });

        if ($report->hasChildren()) {
            $report->setType(Report::TYPE_SUCCESS);
            $report->setMessage('Service restorations found');
        }

        return $report;
    }

    private function compare(callable $trendComparator): Report
    {
        $oldReports = $this->mapReportsById($this->oldReport);
        $newReports = $this->mapReportsById($this->newReport);

        $result = Report::createInfo();

        foreach ($oldReports as $reportId => $oldReport) {
            if (!isset($newReports[$reportId])) {
                continue;
            }
            $trend = $this->getTrend($oldReport, $newReports[$reportId]);
            if ($trendComparator($trend)) {
                $result->add($newReports[$reportId]);
            }
        }

        if (!$result->hasChildren()) {
            $result->setMessage('No changes found');
        }

        return $result;
    }

    /**
     * @param Report $oldReport
     * @param Report $newReports
     * @return int
     */
    private function getTrend(Report $oldReport, Report $newReports): int
    {
        return self::REPORT_WEIGHT_MAP[$oldReport->getType()] - self::REPORT_WEIGHT_MAP[$newReports->getType()];
    }

    /**
     * @param Report $report
     * @return Report[]
     */
    private function mapReportsById(Report $report): array
    {
        $result = [];
        foreach ($report->getChildren() as $childReport) {
            $reportData = $childReport->getData();
            if (isset($reportData[CheckInterface::PARAM_CHECK_ID])) {
                $result[$reportData[CheckInterface::PARAM_CHECK_ID]] = $childReport;
            }
        }
        ksort($result);
        return $result;
    }
}
