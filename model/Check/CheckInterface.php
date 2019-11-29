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

namespace oat\taoSystemStatus\model\Check;

use oat\oatbox\action\Action;
use common_report_Report as Report;

/**
 * Interface CheckInterface
 * @package oat\taoSystemStatus\model\Check
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
interface CheckInterface extends Action
{
    /** @var string per instance check */
    const TYPE_INSTANCE = 'instance';

    /** @var string common check */
    const TYPE_SYSTEM = 'system';

    /**
     * @param $params
     * @return Report
     */
    public function __invoke($params): Report;

    /**
     * See TYPE_* constants
     * @return string
     */
    public function getType(): string;

    /**
     * Unique identifier. In most cases it may be fully classified class name
     * @return string
     */
    public function getId(): string;

    /**
     * Is check active and should be run.
     * Some of checks may need to launched only once, right after instance is ready
     * Or some checks makes sense only on worker server, so on web server it may be inactive
     * @return string
     */
    public function isActive(): string;

}
