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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 *
 */

namespace oat\taoSystemStatus\controller;

use oat\taoSystemStatus\model\Monitoring\ExecutionsStatistics;
use oat\taoSystemStatus\model\SystemStatusException;

/**
 * Class PerformanceMonitoring
 * @package oat\taoSystemStatus\controller
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class PerformanceMonitoring extends \tao_actions_SinglePageModule
{
    const PARAM_INTERVAL = 'interval';
    const DEFAULT_INTERVAL = 'PT1H';

    public function index()
    {
        $this->setView('PerformanceMonitoring/index.tpl');
    }

    public function executionsStatistics()
    {
        $params = $this->getPsrRequest()->getQueryParams();
        $interval = isset($params[self::PARAM_INTERVAL]) ? $params[self::PARAM_INTERVAL] : self::DEFAULT_INTERVAL;
        $service = $this->getServiceLocator()->get(ExecutionsStatistics::SERVICE_ID);
        if (!in_array($interval, ['PT1H', 'P1D', 'P1M'])) {
            throw new \common_exception_BadRequest('Allowed intervals: \'PT1H\', \'P1D\', \'P1M\'');
        }

        $period = $this->getPeriod($interval);
        try {
            $startedExecutionsData = $service->getStartedExecutionsData($period);
            $finishedExecutionsData = $service->getFinishedExecutionsData($period);
        } catch (SystemStatusException $e) {
            throw new \common_exception_NoImplementation('No delivery monitoring service implementation');
        }

        $result = [];
        foreach ($startedExecutionsData as $key => $executionsData) {
            $result['time'][] = $executionsData['time'];
            $result['started'][] = $executionsData['count'];
            $result['finished'][] = $finishedExecutionsData[$key]['count'];
        }

        $this->returnJson($result);
    }

    /**
     * @param string $interval
     * @return \DatePeriod
     * @throws \Exception
     */
    private function getPeriod(string $interval)
    {
        $begin = new \DateTime('now');
        switch ($interval) {
            case 'PT1H':
                $begin = $begin->modify('-1 hour');
                $interval = new \DateInterval('PT1M');
                $amount = 60;
                break;
            case 'P1D':
                $begin = $begin->modify('-1 day');
                $interval = new \DateInterval('PT10M');
                $amount = 24 * 6;
                break;
            case 'P1M':
                $begin = $begin->modify('-30 days');
                $interval = new \DateInterval('PT6H');
                $amount = 30 * 4;
                break;
            default:
                $interval = new \DateInterval('PT1M');
                $amount = 60;
                $begin = $begin->modify('-1 hour');

        }
        return new \DatePeriod($begin, $interval, $amount);
    }
}
