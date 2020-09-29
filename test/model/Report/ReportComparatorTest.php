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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\test\model\Report;

use common_report_Report as Report;
use oat\generis\test\TestCase;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\Report\ReportComparator;

/**
 * Class ReportComparatorTest
 * @package oat\taoSystemStatus\test\model\SystemStatus
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ReportComparatorTest extends TestCase
{

    public function testGetDegradations()
    {
        $oldReport = $this->getInitialReport();
        $newReport = $this->getNewReport();

        $expected = new Report(Report::TYPE_INFO);
        $expected->add($this->getReport(Report::TYPE_WARNING, 'Check A warning', 'A'));
        $expected->add($this->getReport(Report::TYPE_ERROR, 'Check E error', 'E'));
        $expected->add($this->getReport(Report::TYPE_ERROR, 'Check G error', 'G'));

        $comparator = new ReportComparator($oldReport, $newReport);
        $this->assertEquals($expected, $comparator->getDegradations());

        //No degradations - empty report
        $comparator = new ReportComparator($oldReport, $oldReport);
        $this->assertEquals(new Report(Report::TYPE_INFO), $comparator->getDegradations());
    }

    public function testGetRestorations()
    {
        $oldReport = $this->getInitialReport();
        $newReport = $this->getNewReport();

        $expected = new Report(Report::TYPE_INFO);
        $expected->add($this->getReport(Report::TYPE_SUCCESS, 'Check B success', 'B'));
        $expected->add($this->getReport(Report::TYPE_WARNING, 'Check C warning', 'C'));
        $expected->add($this->getReport(Report::TYPE_SUCCESS, 'Check F success', 'F'));

        $comparator = new ReportComparator($oldReport, $newReport);
        $this->assertEquals($expected, $comparator->getRestorations());
    }

    private function getInitialReport()
    {
        $oldReport = new Report(Report::TYPE_INFO);
        $oldReport->add($this->getReport(Report::TYPE_SUCCESS, 'Check A success', 'A'));
        $oldReport->add($this->getReport(Report::TYPE_WARNING, 'Check B warning', 'B'));
        $oldReport->add($this->getReport(Report::TYPE_ERROR, 'Check C error', 'C'));
        $oldReport->add($this->getReport(Report::TYPE_SUCCESS, 'Check D success', 'D'));
        $oldReport->add($this->getReport(Report::TYPE_WARNING, 'Check E warning', 'E'));
        $oldReport->add($this->getReport(Report::TYPE_ERROR, 'Check F error', 'F'));
        $oldReport->add($this->getReport(Report::TYPE_SUCCESS, 'Check G success', 'G'));

        return $oldReport;
    }

    private function getNewReport()
    {
        $newReport = new Report(Report::TYPE_INFO);
        $newReport->add($this->getReport(Report::TYPE_WARNING, 'Check A warning', 'A'));//success -> warning -1
        $newReport->add($this->getReport(Report::TYPE_SUCCESS, 'Check B success', 'B'));//warning -> success +1
        $newReport->add($this->getReport(Report::TYPE_WARNING, 'Check C warning', 'C'));//error   -> warning +1
        $newReport->add($this->getReport(Report::TYPE_SUCCESS, 'Check D success', 'D'));//success -> success  0
        $newReport->add($this->getReport(Report::TYPE_ERROR, 'Check E error', 'E'));    //warning -> error   -1
        $newReport->add($this->getReport(Report::TYPE_SUCCESS, 'Check F success', 'F'));//error   -> success +2
        $newReport->add($this->getReport(Report::TYPE_ERROR, 'Check G error', 'G'));    //success   -> error -2
        return $newReport;
    }

    private function getReport($type, $message, $id = null): Report
    {
        return new Report($type, $message, [
            CheckInterface::PARAM_CHECK_ID => $id,
            CheckInterface::PARAM_DETAILS => $id,
        ]);
    }
}