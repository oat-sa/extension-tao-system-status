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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 *
 */

namespace oat\taoSystemStatus\controller;

use common_report_Report as Report;

/**
 * Class Status
 *
 * @package oat\taoSystemStatus\controller
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class SystemStatus extends \tao_actions_SinglePageModule
{

    /**
     * Show system status by category
     */
    public function index()
    {
        $this->setView('Status/index.tpl');
    }

    public function reports()
    {
        $params = $this->getPsrRequest()->getQueryParams();

        if (isset($params['category'])) {
            $report = new Report(Report::TYPE_WARNING, 'Partially Degraded Service', ['category' => $params['category']]);
            $report->add(new Report(Report::TYPE_SUCCESS, 'Service A operational', ['category' => $params['category']]));
            $report->add(new Report(Report::TYPE_WARNING, 'Service B operational', ['category' => $params['category'], 'details' => 'Average time of response above average']));
            $report->add(new Report(Report::TYPE_SUCCESS, 'Service C operational', ['category' => $params['category']]));
            $report->add(new Report(Report::TYPE_SUCCESS, 'Service D operational', ['category' => $params['category']]));
            $report->add(new Report(Report::TYPE_INFO,    'Service E operational', ['category' => $params['category'], 'details' => '12 days of uptime']));
            $report->add(new Report(Report::TYPE_ERROR,   'Service F operational', ['category' => $params['category'], 'details' => 'Partially Degraded Service']));
        } else {
            $report = new Report(Report::TYPE_ERROR, 'Partially Degraded Service', ['category' => 'Tao System']);
            $report->add(new Report(Report::TYPE_SUCCESS, 'All Systems Operational', ['category' => 'Tao System']));
            $report->add(new Report(Report::TYPE_WARNING, 'The system is not configured optimally', ['category' => 'Tao configuration']));
            $report->add(new Report(Report::TYPE_ERROR, '2 tasks were failed during last 24 hours', ['category' => 'Task queue']));
        }

        $this->returnJson([
            'report' => $report
        ]);
    }

}