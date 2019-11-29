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

namespace oat\taoSystemStatus\test\model\Check;

use oat\taoSystemStatus\model\Check\AbstractCheck;
use common_report_Report as Report;

/**
 * class SampleCheck
 * @package oat\taoSystemStatus\test\model\Check
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class SampleCheck extends AbstractCheck
{
    public function __invoke($params): Report
    {
        return Report::createSuccess('foo');
    }
    public function isActive(): bool
    {
        return true;
    }
}