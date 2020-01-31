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

use common_exception_Error;
use common_exception_NotFound;
use InvalidArgumentException;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\TaskLogFilter;
use oat\taoAct\model\transmissionLog\TransmissionLog;
use oat\taoAct\model\transmissionLog\TransmissionLogService;
use oat\taoDelivery\model\execution\rds\RdsDeliveryExecutionService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringService;
use common_report_Report as Report;
use common_ext_ExtensionsManager;

/**
 * Class DeliveryExecutionReport
 *
 * Show comprehensive information about delivery execution
 *
 * Run example:
 * ```
 * sudo php index.php 'oat\taoSystemStatus\scripts\tools\DeliveryExecutionReport' http://act-pr.docker.localhost/tao.rdf#i5e28154860c9a17943be5add4f7b1b5
 * ```
 *
 * @package oat\taoSystemStatus\scripts\tools
 * @author Andrei Niahrou, <Andrei.Niahrou@1pt.com>
 */
class DeliveryExecutionReport extends AbstractAction
{
    use LoggerAwareTrait;

    /** @var string */
    private $executionDeliveryId;

    /**
     * @inheritdoc
     */
    public function __invoke($params = [])
    {
        $this->init($params);
        return $this->execute($this->executionDeliveryId);
    }

    /**
     * @param array $params
     * @throws
     */
    private function init($params = [])
    {
        if (!isset($params[0])) {
            throw new InvalidArgumentException('Missing parameter');
        }
        $this->executionDeliveryId = $params[0];
    }

    /**
     * @param string $executionId
     * @return Report
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    private function execute($executionId)
    {
        $report = new Report(Report::TYPE_INFO, sprintf('Information about delivery execution %s', $executionId));
        $extensionManager = $this->getExtensionManager();

        $deliveryExecutionService = $this->getDeliveryExecutionService();
        $deliveryExecution = $deliveryExecutionService->getDeliveryExecution($executionId);
        $this->addSubReport($report, 'Delivery execution report', [
            RdsDeliveryExecutionService::COLUMN_LABEL => $deliveryExecution->getLabel(),
            RdsDeliveryExecutionService::COLUMN_DELIVERY_ID => $deliveryExecution->getDelivery()->getUri(),
            RdsDeliveryExecutionService::COLUMN_USER_ID => $deliveryExecution->getUserIdentifier(),
            RdsDeliveryExecutionService::COLUMN_STATUS => $deliveryExecution->getState()->getLabel(),
            RdsDeliveryExecutionService::COLUMN_STARTED_AT => $this->getFormatDate($deliveryExecution->getStartTime()),
            RdsDeliveryExecutionService::COLUMN_FINISHED_AT => $this->getFormatDate($deliveryExecution->getFinishTime())
        ]);

        if ($extensionManager->isInstalled('taoProctoring')) {
            $deliveryLog = $this->getDeliveryLog();
            $deliveryLogEvents = $deliveryLog->search([DeliveryLog::DELIVERY_EXECUTION_ID => $executionId], [
                'order' => DeliveryLog::CREATED_AT,
                'dir' => 'asc',
            ]);
            $this->addSubReport($report, 'Delivery log report', $deliveryLogEvents);

            $monitoringService = $this->getDeliveryMonitoringService();
            $deliveryMonitoringData = $monitoringService->getData($deliveryExecution)->get();
            $this->addSubReport($report, 'Delivery monitoring report', $deliveryMonitoringData);
        }

        if ($extensionManager->isInstalled('taoAct')) {
            $transmissionLogService = $this->getTransmissionLogService();
            $odsEvents = $transmissionLogService->findTransmissions([
                TransmissionLog::COLUMN_SESSION_ID => $this->getIdentifier($executionId)
            ]);
            $this->addSubReport($report, 'Ods log report', $odsEvents);
        }

        $taskLog = $this->getTaskLog();
        $optionCondition = sprintf('%%%s%%', $this->getIdentifier($executionId));
        $filter = (new TaskLogFilter())
            ->like(TaskLogBrokerInterface::COLUMN_PARAMETERS, $optionCondition)
            ->gte(TaskLogBrokerInterface::COLUMN_CREATED_AT, $this->getFormatDate($deliveryExecution->getStartTime()));
        $taskLogInform = $taskLog->search($filter)->toArray();
        $this->addSubReport($report, 'Tasks log report', $taskLogInform);

        return $report;
    }

    /**
     * Add a subReport to the main report
     * @param Report $report
     * @param mixed $data
     * @param string $message
     * @throws common_exception_Error
     */
    private function addSubReport($report, $message, $data)
    {
        $message .= ': ' . json_encode($data, JSON_PRETTY_PRINT);
        $deliveryExecutionReport = new Report(Report::TYPE_INFO, $message);
        $deliveryExecutionReport->setData($data);
        $report->add($deliveryExecutionReport);
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
     * Returns identifier from full uri
     * @param string $uri
     * @return string
     */
    private function getIdentifier($uri)
    {
        return substr($uri, strpos($uri, '#') + 1);
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
     * @return TaskLog|object
     */
    private function getTaskLog()
    {
        return $this->getServiceLocator()->get(TaskLog::SERVICE_ID);
    }

    /**
     * @return TransmissionLogService|object
     */
    private function getTransmissionLogService()
    {
        return $this->getServiceLocator()->get(TransmissionLogService::SERVICE_ID);
    }

}
