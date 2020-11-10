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

namespace oat\taoSystemStatus\test\model\Check\System;

use common_report_Report as Report;
use oat\generis\test\TestCase;
use oat\taoSystemStatus\model\Check\System\PHPSessionTtlCheck;

/**
 * Class PHPSessionTtlCheckTest
 *
 * Warning when cookie life time is less than session life time.
 * Warning when session life time has default value (that means that this value was not adapted to customer needs)
 * Warning when session life time less than default. This means that it's configured wrong.
 *
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class PHPSessionTtlCheckTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testInvoke()
    {
        $check = new PHPSessionTtlCheck();
        ini_set('session.gc_maxlifetime','1441');
        ini_set('session.cookie_lifetime','0');

        $report = $check();
        $this->assertEquals(Report::TYPE_SUCCESS, $report->getType());

        ini_set('session.gc_maxlifetime','1440');
        ini_set('session.cookie_lifetime','0');

        $report = $check();
        $this->assertEquals(Report::TYPE_WARNING, $report->getType());
        $this->assertEquals(
            '\'session.gc_maxlifetime\' php option has default value. Session life time is 1440 seconds',
            $report->getMessage()
        );

        ini_set('session.gc_maxlifetime','1439');
        ini_set('session.cookie_lifetime','0');

        $report = $check();
        $this->assertEquals(Report::TYPE_WARNING, $report->getType());
        $this->assertEquals(
    '\'session.gc_maxlifetime\' php option is less than default value. Session life time is 1439 seconds',
            $report->getMessage()
        );

        ini_set('session.gc_maxlifetime','1441');
        ini_set('session.cookie_lifetime','1439');

        $report = $check();
        $this->assertEquals(Report::TYPE_WARNING, $report->getType());
        $this->assertEquals(
            '\'session.cookie_lifetime\' php option is less than \'session.gc_maxlifetime\'. Session life time is 1439 seconds',
            $report->getMessage()
        );

        ini_set('session.gc_maxlifetime','1440');
        ini_set('session.cookie_lifetime','1439');

        $report = $check();
        $this->assertEquals(Report::TYPE_WARNING, $report->getType());
        $this->assertEquals(
   '\'session.gc_maxlifetime\' php option has default value. Session life time is 1440 seconds' . PHP_EOL .
            '\'session.cookie_lifetime\' php option is less than \'session.gc_maxlifetime\'. Session life time is 1439 seconds',
            $report->getMessage()
        );
    }
}
