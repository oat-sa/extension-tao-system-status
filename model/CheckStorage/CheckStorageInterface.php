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

namespace oat\taoSystemStatus\model\CheckStorage;

use oat\taoSystemStatus\model\Check\CheckInterface;

/**
 * Interface CheckStorageInterface
 *
 * Check storage used to register checks and their parameters so they can be available for SystemStatus services.
 *
 * @package oat\taoSystemStatus\model\CheckStorage
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
interface CheckStorageInterface
{
    /**
     * @param CheckInterface $check
     * @return mixed
     */
    public function add(CheckInterface $check);

    /**
     * @param CheckInterface $check
     * @return mixed
     */
    public function remove(CheckInterface $check);

    /**
     * @param string $type
     * @return mixed
     */
    public function getChecks(string $type);

    /**
     * @param $persistence \common_persistence_Persistence
     * @return boolean
     */
    public static function install($persistence): bool;
}