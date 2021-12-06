<?php

use oat\taoSystemStatus\model\Check\System\StuckTasksCheckService;

return new \oat\taoSystemStatus\model\Check\System\StuckTasksCheckService([
    StuckTasksCheckService::RUNNING_MAX_TIME => 2, // max time for running status, hours
    StuckTasksCheckService::ENQUEUED_MAX_TIME => 24, // max time for enqueued status, hours
]);
