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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\oatbox\log\loggerawaretrait;
use DateInterval;
use DateTime;
use Aws\ElastiCache\ElastiCacheClient;
use oat\taoSystemStatus\model\SystemCheckException;
use oat\awsTools\AwsClient;
use oat\taoSystemStatus\model\Check\Traits\PieChartReportRenderer;

/**
 * Class PHPSessionTtlCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class PHPSessionTtlCheck extends AbstractCheck
{
    use LoggerAwareTrait;


    /**
     * @param array $params
     * @return Report
     * @throws \Exception
     */
    public function __invoke($params = []): Report
    {
        $sessionMaxLifetime = (int) ini_get('session.gc_maxlifetime');
        $cookieLifetime = (int) ini_get('session.cookie_lifetime');

        if ($cookieLifetime !== 0 && $cookieLifetime < $sessionMaxLifetime) {
            $report = new Report(
                Report::TYPE_WARNING,
                __('\'session.cookie_lifetime\' php option is less than \'session.gc_maxlifetime\'. Session life time is %d seconds', $cookieLifetime)
            );
        } else if ($sessionMaxLifetime === 1440 ) {
            $report = new Report(
                Report::TYPE_WARNING,
                __('\'session.gc_maxlifetime\' php option has default value. Session life time is %d seconds', $sessionMaxLifetime)
            );
        } else if ($sessionMaxLifetime < 1440 ) {
            $report = new Report(
                Report::TYPE_WARNING,
                __('\'session.gc_maxlifetime\' php option is less than default value. Session life time is %d seconds', $sessionMaxLifetime)
            );
        } else {
            $report = new Report(
                Report::TYPE_SUCCESS,
                __('Session life time is %d seconds', min($cookieLifetime, $sessionMaxLifetime)));
        }

        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
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
        return __('Health/Readiness check');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('PHP session lifetime');
    }

}
