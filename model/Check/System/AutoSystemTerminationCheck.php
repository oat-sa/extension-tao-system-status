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

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use Exception;
use oat\taoProctoring\model\FinishDeliveryExecutionsService;
use oat\taoProctoring\model\implementation\DeliveryExecutionStateService;
use oat\taoProctoring\model\Tasks\FinishDeliveryExecutionsTask;
use oat\taoProctoring\model\Tasks\TerminateDeliveryExecutionsTask;
use oat\taoProctoring\model\TerminateDeliveryExecutionsService;
use oat\taoProctoring\scripts\TerminateNotStartedAssessment;
use oat\taoProctoring\scripts\TerminatePausedAssessment;
use oat\taoScheduler\model\scheduler\SchedulerService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class AutoSystemTerminationCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class AutoSystemTerminationCheck extends AbstractCheck
{
    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $jobs = $this->getSchedulerService()->getJobs();
        $reportText = '';
        foreach ($jobs as $job) {
            $params = $job->getParams();
            if (in_array(TerminatePausedAssessment::class, $params, true)) {
                $cancellationDelay = $this->getDeliveryExecutionStateService()->getOption(DeliveryExecutionStateService::OPTION_CANCELLATION_DELAY);
                $reportText .= __('TerminatePausedAssessment job is scheduled with delivery execution TTL = %s', $this->getHumanInterval($cancellationDelay)). PHP_EOL;
            }

            if (in_array(TerminateNotStartedAssessment::class, $params, true)) {
                $terminationDelay = $this->getDeliveryExecutionStateService()->getOption(DeliveryExecutionStateService::OPTION_TERMINATION_DELAY_AFTER_PAUSE);
                $reportText .= __('TerminateNotStartedAssessment job is scheduled with delivery execution TTL = %s', $this->getHumanInterval($terminationDelay)). PHP_EOL;
            }

            if (in_array(TerminateDeliveryExecutionsTask::class, $params, true)) {
                $cancellationDelay = $this->getTerminateDeliveryExecutionsService()->getOption(TerminateDeliveryExecutionsService::OPTION_TTL_AS_ACTIVE);
                $reportText .= __('TerminateDeliveryExecutionsTask job is scheduled with delivery execution TTL = %s', $this->getHumanInterval($cancellationDelay)). PHP_EOL;
            }

            if (in_array(FinishDeliveryExecutionsTask::class, $params, true)) {
                $cancellationDelay = $this->getFinishDeliveryExecutionsService()->getOption(FinishDeliveryExecutionsService::OPTION_TTL_AS_ACTIVE);
                $reportText .= __('FinishDeliveryExecutionsTask job is scheduled with delivery execution TTL = %s', $this->getHumanInterval($cancellationDelay)). PHP_EOL;
            }
        }

        if (!$reportText) {
            return new Report(Report::TYPE_INFO, __('No termination tasks.'));
        }

        return new Report(Report::TYPE_INFO, $reportText);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->ifTaoSchedulerIsInstalled();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_SYSTEM;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('Configuration Values');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Auto cancellation/termination configuration');
    }

    /**
     * @return DeliveryExecutionStateService
     */
    private function getDeliveryExecutionStateService() : DeliveryExecutionStateService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(DeliveryExecutionStateService::SERVICE_ID);
    }

    /**
     * @return bool
     */
    private function ifTaoSchedulerIsInstalled() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('taoScheduler');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService() : SchedulerService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(SchedulerService::SERVICE_ID);
    }

    /**
     * @return TerminateDeliveryExecutionsService
     */
    private function getTerminateDeliveryExecutionsService() : TerminateDeliveryExecutionsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(TerminateDeliveryExecutionsService::SERVICE_ID);
    }

    /**
     * @return FinishDeliveryExecutionsService
     */
    private function getFinishDeliveryExecutionsService() : FinishDeliveryExecutionsService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(FinishDeliveryExecutionsService::SERVICE_ID);
    }

    /**
     * @param string $interval
     * @return string
     * @throws Exception
     */
    private function getHumanInterval($interval) : string
    {
        return \tao_helpers_Date::displayInterval(new \DateInterval($interval));
    }
}
