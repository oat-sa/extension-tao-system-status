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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoSystemStatus\scripts\tools;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\notifications\AlarmNotificationService;
use oat\tao\model\notifications\Alert;
use oat\taoSystemStatus\model\Report\ReportComparator;
use oat\taoSystemStatus\model\SystemStatus\InstanceStatusService;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use oat\oatbox\reporting\Report;

/**
 * Class CheckDegradations
 *
 * ```bash
 * $ sudo -u www-data php index.php '\oat\taoSystemStatus\scripts\tools\CheckDegradations' -p default_kv
 * ```
 *
 * @package oat\taoSystemStatus\scripts\tools
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class CheckDegradations extends ScriptAction
{
    private const KV_PERSISTENCE_KEY = self::class.'::systems_status_report';

    /**
     * @return array[]
     */
    public function provideOptions()
    {
        return [
            'persistence' => [
                'prefix' => 'p',
                'longPrefix' => 'persistence',
                'required' => true,
                'description' => 'The KeyValue persistence identifier (see your persistence config) where you want store results of previous check',
            ],
        ];
    }

    /**
     * @return string
     */
    public function provideDescription()
    {
        return 'Script checks if there are any degradations in the system status since last launch';
    }

    public function run()
    {
        $this->getInstanceStatusService()->check();
        $report = $this->getSystemStatusService()->check();
        $previousReport = $this->getPreviousReport();
        if (!$previousReport) {
            $result = new Report(Report::TYPE_INFO, 'No previous report found');
        } else {
            $comparator = new ReportComparator($previousReport, $report);
            $result = $comparator->getDegradations();

            if (in_array($result->getType(), [Report::TYPE_WARNING, Report::TYPE_ERROR])) {
                $result->add($this->sendAlert($result));
            }
        }

        $this->getPersistence()->set(self::KV_PERSISTENCE_KEY, json_encode($report));
        return $result;
    }

    /**
     * @return array|string[]
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * @return Report|null
     * @throws \common_exception_Error
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    private function getPreviousReport():?Report
    {
        $report = $this->getPersistence()->get(self::KV_PERSISTENCE_KEY);
        $result = null;
        if ($report) {
            $report = json_decode($report, true);
            $result = Report::jsonUnserialize($report);
        }
        return $result;
    }

    /**
     * @param Report $report
     * @return Report
     */
    private function sendAlert(Report $report)
    {
        /** @var AlarmNotificationService $alertService */
        $alertService = $this->getServiceLocator()->get(AlarmNotificationService::SERVICE_ID);
        $alert = new Alert(
            'System degradations detected: ' . ROOT_URL,
            json_encode($report->toArray(), JSON_PRETTY_PRINT)
        );
        $alertService->sendNotifications($alert);

        return Report::createInfo('Alert has been sent');
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    private function getPersistence()
    {
        $id = $this->getOption('persistence');
        return $this->getServiceManager()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById($id);
    }

    /**
     * @return SystemStatusService
     */
    protected function getSystemStatusService() : SystemStatusService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
    }

    /**
     * @return InstanceStatusService
     */
    private function getInstanceStatusService() : InstanceStatusService
    {
        return $this->getServiceLocator()->get(InstanceStatusService::SERVICE_ID);
    }
}
