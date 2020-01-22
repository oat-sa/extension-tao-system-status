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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 *
 */

use oat\tao\model\user\TaoRoles;

return [
    'name' => 'taoSystemStatus',
    'label' => 'TAO System Status',
    'description' => 'TAO System Status',
    'license' => 'GPL-2.0',
    'version' => '0.6.1',
    'author' => 'Open Assessment Technologies SA',
    'requires' => [
        'tao' => '>=39.4.0',
    ],
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoSystemStatusManager',
    'acl' => [
        ['grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoSystemStatusManager', ['ext' => 'taoSystemStatus']],
        ['grant', TaoRoles::SYSTEM_ADMINISTRATOR, ['ext' => 'taoSystemStatus', 'mod' => 'SystemStatus']],
    ],
    'install' => [
        'php' => [
            \oat\taoSystemStatus\scripts\install\RegisterCheckStorage::class,
            \oat\taoSystemStatus\scripts\install\RegisterChecks::class,
            \oat\taoSystemStatus\scripts\install\RegisterSystemStatusLog::class,
        ]
    ],
    'uninstall' => [],
    'update' => oat\taoSystemStatus\scripts\update\Updater::class,
    'routes' => array(
        '/taoSystemStatus' => 'oat\\taoSystemStatus\\controller'
    ),
    'constants' => [
        'DIR_VIEWS' => __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
        'BASE_URL'  => ROOT_URL . 'taoSystemStatus/',
        'BASE_WWW'  => ROOT_URL . 'taoSystemStatus/views/',
    ],
    'extra' => [
        'structures' => __DIR__ . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml',
    ],
];
