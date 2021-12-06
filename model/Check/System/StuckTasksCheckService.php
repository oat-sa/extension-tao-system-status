<?php

namespace oat\taoSystemStatus\model\Check\System;

use oat\oatbox\service\ConfigurableService;

class StuckTasksCheckService extends ConfigurableService
{
    public const SERVICE_ID = 'taoSystemStatus/TaskQueueCheck';
    public const RUNNING_MAX_TIME = 'runningMaxTime';
    public const ENQUEUED_MAX_TIME = 'enqueuedMaxTime';

    public function getRunningMaxTime(): float
    {
        return $this->getOption(self::RUNNING_MAX_TIME);
    }

    public function getEnqueuedMaxTime(): float
    {
        return $this->getOption(self::ENQUEUED_MAX_TIME);
    }
}
