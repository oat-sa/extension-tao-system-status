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

namespace oat\taoSystemStatus\scripts\update;

use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use common_ext_ExtensionUpdater;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoSystemStatus\model\Monitoring\ExecutionsStatistics;
use oat\tao\model\TaoOntology;

/**
 * Class Updater
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 * @deprecated use migrations instead. See https://github.com/oat-sa/generis/wiki/Tao-Update-Process
 */
class Updater extends common_ext_ExtensionUpdater
{
    /**
     * @param $initialVersion
     * @return string|void
     */
    public function update($initialVersion)
    {
        $this->skip('0.0.1', '0.9.0');

        if ($this->isVersion('0.9.0')) {
            $this->getServiceManager()->register(ExecutionsStatistics::SERVICE_ID, new ExecutionsStatistics([]));
            $this->setVersion('0.10.0');
        }

        $this->skip('0.10.0', '0.10.1');

        if ($this->isVersion('0.10.1')) {
            $rolesService = \tao_models_classes_RoleService::singleton();
            $globalManager = new \core_kernel_classes_Resource(TaoRoles::GLOBAL_MANAGER);
            $systemManager = new \core_kernel_classes_Resource('http://www.tao.lu/Ontologies/generis.rdf#taoSystemStatusManager');
            $rolesService->unincludeRole($globalManager, $systemManager);

            AclProxy::revokeRule(new AccessRule(
                AccessRule::GRANT,
                TaoRoles::SYSTEM_ADMINISTRATOR,
                ['ext' => 'taoSystemStatus', 'mod' => 'SystemStatus']
            ));

            $this->setVersion('0.11.0');
        }

        if ($this->isVersion('0.11.0')) {
            OntologyUpdater::syncModels();
            $this->setVersion('0.11.1');
        }

        $this->skip('0.11.1', '0.11.4');
        
        //Updater files are deprecated. Please use migrations.
        //See: https://github.com/oat-sa/generis/wiki/Tao-Update-Process

        $this->setVersion($this->getExtension()->getManifest()->getVersion());
    }
}

