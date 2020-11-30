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
use oat\taoSystemStatus\model\Alert\Alert;
use oat\taoSystemStatus\model\Alert\AlertService;
use oat\taoSystemStatus\model\Report\ReportComparator;
use oat\taoSystemStatus\model\SystemStatus\InstanceStatusService;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use common_report_Report as Report;

/**
 * Class CheckDegradations
 *
 * ```bash
 * $ sudo -u www-data php index.php '\oat\taoSystemStatus\scripts\tools\CreateAlert' -m 'Task Queue' -d 'Execution of task #42 failed'
 * ```
 *
 * @package oat\taoSystemStatus\scripts\tools
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class CreateAlert extends ScriptAction
{

    /**
     * @return array[]
     */
    public function provideOptions()
    {
        return [
            'message' => [
                'prefix' => 'm',
                'longPrefix' => 'message',
                'required' => true,
                'description' => 'Alert message',
            ],
            'description' => [
                'prefix' => 'd',
                'longPrefix' => 'description',
                'required' => true,
                'description' => 'Alert description',
            ],
        ];
    }

    /**
     * @return string
     */
    public function provideDescription()
    {
        return 'Script sends an alert using configured alert service';
    }

    /**
     * @return Report
     */
    public function run()
    {
        /** @var AlertService $service */
        $service = $this->getServiceLocator()->get(AlertService::SERVICE_ID);
        $alert = new Alert($this->getOption('message'), $this->getOption('description'));
        return $service->createAlert($alert);
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
}
