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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\model\Check\System;

use Aws\Rds\RdsClient;
use oat\awsTools\AwsClient;
use oat\generis\persistence\PersistenceManager;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\taoSystemStatus\model\SystemCheckException;

/**
 * Class AbstractAwsRDSCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Makar Sichevoi, <makar.sichevoy@taotesting.com>
 */
abstract class AbstractAwsRDSCheck extends AbstractCheck
{
    protected const PARAM_DEFAULT_PERIOD = 300;

    /**
     * @return RdsClient
     */
    protected function getRdsClient(): RdsClient
    {
        return new RdsClient($this->getAwsClient()->getOptions());
    }

    /**
     * @param string $rdsHost
     * @return string
     * @throws SystemCheckException
     */
    protected function getStackId(string $rdsHost): string
    {
        $hostParts = explode('.', $rdsHost);

        if (!isset($hostParts[2])) {
            throw new SystemCheckException('Cannot get stack id by rds host');
        }

        return $hostParts[2];
    }

    /**
     * @return null|string
     */
    protected function getRDSHost(): string
    {
        $persistences = $this->getPersistenceManager()
            ->getOption(PersistenceManager::OPTION_PERSISTENCES);

        $host = null;
        foreach ($persistences as $persistence) {
            if (isset($persistence['driver']) && $persistence['driver'] === 'dbal') {
                $host = $persistence['connection']['host'];
            }
        }

        return $host;
    }

    /**
     * @return AwsClient
     */
    protected function getAwsClient(): AwsClient
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get('generis/awsClient');
    }

    /**
     * @return PersistenceManager
     */
    protected function getPersistenceManager(): PersistenceManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
    }
}
