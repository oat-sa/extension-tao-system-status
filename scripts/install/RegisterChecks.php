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
 * Copyright (c) 2019-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\scripts\install;

use oat\oatbox\reporting\Report;
use oat\oatbox\extension\AbstractAction;
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\taoSystemStatus\model\SystemStatusException;
use oat\taoSystemStatus\model\Check\Instance\CronCheck;
use oat\taoSystemStatus\model\Check\System\Act\SNSCheck;
use oat\taoSystemStatus\model\Check\System\TaoLtiKVCheck;
use oat\taoSystemStatus\model\Check\Instance\MathJaxCheck;
use oat\taoSystemStatus\model\Check\System\DebugModeCheck;
use oat\taoSystemStatus\model\Check\System\HeartBeatCheck;
use oat\taoSystemStatus\model\Check\System\TaoUpdateCheck;
use oat\taoSystemStatus\model\Check\System\LoginQueueCheck;
use oat\taoSystemStatus\model\Check\System\CertificateCheck;
use oat\taoSystemStatus\model\Check\System\FrontEndLogCheck;
use oat\taoSystemStatus\model\Check\System\LockServiceCheck;
use oat\taoSystemStatus\model\Check\System\WebSourceTTLCheck;
use oat\taoSystemStatus\model\Check\Instance\WkhtmltopdfCheck;
use oat\taoSystemStatus\model\Check\System\PHPSessionTtlCheck;
use oat\taoSystemStatus\model\Check\Instance\MessagesJsonCheck;
use oat\taoSystemStatus\model\Check\System\LocalNamespaceCheck;
use oat\taoSystemStatus\model\Check\System\TaskQueueFailsCheck;
use oat\taoSystemStatus\model\Check\System\TaskQueueMonitoring;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoSystemStatus\model\Check\System\AwsRDSFreeSpaceCheck;
use oat\taoSystemStatus\model\Check\System\DefaultLanguageCheck;
use oat\taoSystemStatus\model\Check\System\DefaultTimeZoneCheck;
use oat\taoSystemStatus\model\Check\System\TaoLtiDeliveryKVCheck;
use oat\taoSystemStatus\model\Check\Instance\WriteConfigDataCheck;
use oat\taoSystemStatus\model\Check\System\AlarmNotificationCheck;
use oat\taoSystemStatus\model\Check\System\AwsRedisFreeSpaceCheck;
use oat\taoSystemStatus\model\Check\System\FileSystemS3CacheCheck;
use oat\taoSystemStatus\model\Check\System\TaskQueueFinishedCheck;
use oat\taoSystemStatus\model\Check\Instance\ConfigCongruenceS3Check;
use oat\taoSystemStatus\model\Check\System\Act\OdsConfigurationCheck;
use oat\taoSystemStatus\model\Check\System\AutoSystemTerminationCheck;
use oat\taoSystemStatus\model\Check\System\FileSystemS3CachePathCheck;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchStatusCheck;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchIndexationCheck;
use oat\taoSystemStatus\model\Check\System\AdvancedSearch\AdvancedSearchAvailabilityCheck;

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class RegisterChecks extends AbstractAction
{
    /**
     * @param array $params
     *
     * @throws InvalidServiceManagerException
     *
     * @return Report
     */
    public function __invoke($params)
    {
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceManager()->get(SystemStatusService::SERVICE_ID);

        foreach ($this->getSystemChecks() as $check) {
            try {
                $systemStatusService->addCheck($check);
            } catch (SystemStatusException $e) {
                $this->logError($e->getMessage());
            }
        }

        return Report::createSuccess(__('System status checks successfully registered.'));
    }

    /**
     * @return CheckInterface[]
     */
    private function getSystemChecks(): array
    {
        return [
            new FrontEndLogCheck(),
            new TaoLtiKVCheck(),
            new TaoLtiDeliveryKVCheck(),
            new LockServiceCheck(),
            new DefaultLanguageCheck(),
            new DefaultTimeZoneCheck(),
            new LocalNamespaceCheck(),
            new MessagesJsonCheck(),
            new MathJaxCheck(),
            new WkhtmltopdfCheck(),
            new FileSystemS3CacheCheck(),
            new ConfigCongruenceS3Check(),
            new WriteConfigDataCheck(),
            new DebugModeCheck(),
            new TaoUpdateCheck(),
            new TaskQueueFailsCheck(),
            new TaskQueueFinishedCheck(),
            new AwsRedisFreeSpaceCheck(),
            new HeartBeatCheck(),
            new AwsRDSFreeSpaceCheck(),
            new AutoSystemTerminationCheck(),
            new LoginQueueCheck(),
            new OdsConfigurationCheck(),
            new SNSCheck(),
            new CronCheck(),
            new TaskQueueMonitoring(),
            new WebSourceTTLCheck(),
            new PHPSessionTtlCheck(),
            new FileSystemS3CachePathCheck(),
            new AlarmNotificationCheck(),
            new CertificateCheck(),
            new AdvancedSearchStatusCheck(),
            new AdvancedSearchAvailabilityCheck(),
            new AdvancedSearchIndexationCheck(),
        ];
    }
}
