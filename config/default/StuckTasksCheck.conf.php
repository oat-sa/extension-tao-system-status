<?php

use oat\taoSystemStatus\model\SystemStatus\StuckTasksCheckService;

return new oat\taoSystemStatus\model\SystemStatus\StuckTasksCheckService([
    StuckTasksCheckService::RUNNING_MAX_TIME => 120, // max time for running status, minutes
    StuckTasksCheckService::ENQUEUED_MAX_TIME => 1440, // max time for enqueued status, minutes
]);
