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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSystemStatus\scripts\tools;

use oat\oatbox\extension\AbstractAction;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\taoAct\model\Ods\DeliveryLogCacheService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringService;
use common_report_Report as Report;
use common_ext_ExtensionsManager;

/**
 * Class DeliveryExecutionReport
 *
 * @package oat\taoSystemStatus\scripts\tools
 * @author Andrei Niahrou, <Andrei.Niahrou@1pt.com>
 */
class DeliveryExecutionReport extends AbstractAction
{
    use LoggerAwareTrait;

    private static $validMethods = ['show'];

    /** @var array */
    private $params;

    /** @var string */
    private $method;

    /**
     * @inheritdoc
     */
    public function __invoke($params = [])
    {
        $this->init($params);
        return call_user_func_array([$this, $this->method], $this->params);
    }

    /**
     * @param array $params
     * @throws
     */
    private function init($params = [])
    {
        if (!isset($params[0]) || !in_array($params[0], self::$validMethods)) {
            throw new \InvalidArgumentException('Wrong method parameter. Available methods: ' . implode(',', self::$validMethods));
        }
        $this->method = $params[0];
        $this->params = array_slice($params, 1);
    }

    /**
     * Show comprehensive information about delivery execution
     *
     * Run example:
     * ```
     * sudo php index.php 'oat\taoSystemStatus\scripts\tools\DeliveryExecutionReport' show http://act-pr.docker.localhost/tao.rdf#i5e28154860c9a17943be5add4f7b1b5
     * ```
     *
     * @param string $executionId
     * @return Report
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    private function show($executionId)
    {
        $extensionManager = $this->getExtensionManager();
        $deliveryExecutionService = $this->getDeliveryExecutionService();
        $deliveryExecution = $deliveryExecutionService->getDeliveryExecution($executionId);
        $report = new Report(Report::TYPE_INFO, sprintf('Information about delivery execution %s', $executionId));

        $report->add(
            new Report(
                Report::TYPE_INFO,
                'Delivery Id: ' . $deliveryExecution->getDelivery()->getUri() . PHP_EOL .
                '  User Id: ' . $deliveryExecution->getUserIdentifier() . PHP_EOL .
                '  Status: ' . $deliveryExecution->getState()->getLabel() . PHP_EOL .
                '  Start Time: ' . $this->getFormatDate($deliveryExecution->getStartTime()) . PHP_EOL .
                '  Finish Time: ' . $this->getFormatDate($deliveryExecution->getFinishTime()) . PHP_EOL .
                '  Label: ' . $deliveryExecution->getLabel() . PHP_EOL
            )
        );

        if ($extensionManager->isInstalled('taoProctoring')) {
            $deliveryLog = $this->getDeliveryLog();
            $deliveryLogEvents = $deliveryLog->search([DeliveryLog::DELIVERY_EXECUTION_ID => $executionId], [
                'order' => DeliveryLog::CREATED_AT,
                'dir' => 'asc',
            ]);
            $report->setData($deliveryLogEvents);
            $monitoringService = $this->getDeliveryMonitoringService();
            $deliveryMonitoringData = $monitoringService->getData($deliveryExecution)->get();
            $report->setData($deliveryMonitoringData);
        }

        if ($extensionManager->isInstalled('taoAct')) {
            $odsTaoActDeliveryLogCacheService = $this->getDeliveryLogCacheService();
            $odsEvents = $odsTaoActDeliveryLogCacheService->get($executionId);
            $report->setData($odsEvents);
        }

        $taskLog = $this->getTaskLog();
        $optionCondition = sprintf('%%%s%%', strstr($executionId, '#'));
        $filter = (new TaskLogFilter())
            ->like(TaskLogBrokerInterface::COLUMN_PARAMETERS, $optionCondition)
            ->gte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $this->getFormatDate($deliveryExecution->getStartTime()));
        $taskLogInform = $taskLog->search($filter)->toArray();
        $report->setData($taskLogInform);

        return $report;
    }

    /**
     * Returns a formatted date from a microtime date
     * @param string $microtime
     * @param string $format
     * @return string
     */
    private function getFormatDate($microtime, $format = 'Y-m-d H:i:s')
    {
        date_default_timezone_set('UTC');
        $timestamp = explode(' ', $microtime){1};
        return date($format, $timestamp);
    }

    /**
     * @return common_ext_ExtensionsManager|object
     */
    private function getExtensionManager()
    {
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

    /**
     * @return ServiceProxy|object
     */
    private function getDeliveryExecutionService()
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }

    /**
     * @return DeliveryLog|object
     */
    private function getDeliveryLog()
    {
        return $this->getServiceLocator()->get(DeliveryLog::SERVICE_ID);
    }

    /**
     * @return DeliveryMonitoringService|object
     */
    private function getDeliveryMonitoringService()
    {
        return $this->getServiceLocator()->get(DeliveryMonitoringService::SERVICE_ID);
    }

    /**
     * @return DeliveryLogCacheService|object
     */
    private function getDeliveryLogCacheService()
    {
        return $this->getServiceLocator()->get(DeliveryLogCacheService::SERVICE_ID);
    }

    /**
     * @return TaskLog|object
     */
    private function getTaskLog()
    {
        return $this->getServiceLocator()->get(TaskLog::SERVICE_ID);
    }

}
