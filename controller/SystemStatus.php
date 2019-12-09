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
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

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
        $this->setData('reports', $this->getSystemStatusService()->check());
        $this->setData('support_portal_link', $this->getSystemStatusService()->getSupportPortalLink());
        $this->setData('service', $this->getSystemStatusService());
        $this->setView('Status/index.tpl');
    }

    /**
     * Get system status report
     */
    public function reports()
    {
        $params = $this->getPsrRequest()->getQueryParams();
        /** @var Report[] $reports */
        $reports = $this->getSystemStatusService()->check();

        if (isset($params['category'])) {
            //todo: filter reports by category
        }

        $this->returnJson(['report' => $reports]);
    }

    /**
     * @return SystemStatusService
     */
    protected function getSystemStatusService()
    {
        return $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
    }
}
