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
use oat\tao\model\notifications\AlarmNotificationService;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class AlarmNotificationCheck
 * @package oat\taoSystemStatus\model\Check\System
 */
class AlarmNotificationCheck extends AbstractCheck
{
    /**
     * @param array $params
     * @return Report
     * @throws Exception
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }
        $report = $this->checkNotifiers();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return $this->getServiceLocator()->has(AlarmNotificationService::SERVICE_ID);
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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Alarm communication channels');
    }

    /**
     * @return Report
     * @throws Exception
     */
    private function checkNotifiers() : Report
    {
        $correct = true;
        /** @var AlarmNotificationService $alarmNotificationService */
        $alarmNotificationService = $this->getServiceLocator()->get(AlarmNotificationService::SERVICE_ID);

        $notifiers = $alarmNotificationService->getOption(AlarmNotificationService::OPTION_NOTIFIERS);

        if (is_array($notifiers) && !empty($notifiers)) {
            foreach ($notifiers as $notifier) {
                $correct = $correct && class_exists($notifier['class']);
            }
        } else {
            $correct = false;
        }

        if ($correct) {
            $report = new Report(Report::TYPE_SUCCESS, 'Alarm notification channel configured correctly');
        } else {
            $report = new Report(Report::TYPE_ERROR, 'Alarm notification channel not configured');
        }

        return $report;
    }
}
