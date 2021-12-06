<?php

namespace oat\taoSystemStatus\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoSystemStatus\model\Check\System\StuckTasksCheck;
use oat\taoSystemStatus\model\SystemStatus\SystemStatusService;

class SetUpStuckTasksCheck extends InstallAction
{
    public function __invoke($params)
    {
        $check = new StuckTasksCheck([]);
        /** @var SystemStatusService $systemStatusService */
        $systemStatusService = $this->getServiceLocator()->get(SystemStatusService::SERVICE_ID);
        $systemStatusService->addCheck($check);
    }
}
