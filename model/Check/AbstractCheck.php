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

use common_report_Report as Report;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ServiceManagerAwareTrait;
use common_ext_ExtensionsManager;
use oat\tao\helpers\Template;
use common_Exception;
use Renderer;

/**
 * class AbstractCheck
 * @package oat\taoSystemStatus\test\model\Check
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractCheck implements CheckInterface
{
    use ServiceManagerAwareTrait;
    use LoggerAwareTrait;

    /** @var array  */
    private $params;

    /**
     * AbstractCheck constructor.
     * @param $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            $report = new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        } else {
            $report = $this->doCheck();
        }
        return $this->prepareReport($report);
    }

    /**
     * @inheritdoc
     */
    abstract public function isActive(): bool;

    /**
     * @inheritdoc
     */
    abstract public function getType(): string;

    /**
     * @inheritdoc
     */
    abstract public function getCategory(): string;

    /**
     * @inheritdoc
     */
    abstract public function getDetails(): string;

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return static::class;
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     * @throws common_Exception
     */
    public function renderReport(Report $report): string
    {
        $renderer = new Renderer($this->getTemplate());
        $renderer->setData('report', $report);
        return $renderer->render();
    }

    /**
     * @return Report
     */
    abstract protected function doCheck(): Report;

    /**
     * @return string
     */
    protected function getTemplate() : string
    {
        return Template::getTemplate('Reports/default.tpl', 'taoSystemStatus');
    }

    /**
     * Add metadata to the report, such as category, details etc.
     * @param Report $report
     * @return Report
     */
    protected function prepareReport(Report $report): Report
    {
        $data = $report->getData();
        if ($data === null) {
            $data = [];
        }
        $data[self::PARAM_CATEGORY] = $this->getCategory();
        $data[self::PARAM_DETAILS] = $this->getDetails();
        $data[self::PARAM_CHECK_ID] = $this->getId();
        $data[self::PARAM_DATE] = time();
        $report->setData($data);
        return $report;
    }


    /**
     * @return common_ext_ExtensionsManager
     */
    protected function getExtensionsManagerService() : common_ext_ExtensionsManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

    /**
     * Check if current instance is a worker
     *
     * NOTE: In debug mode all instances treated as workers because this is the most probably developer instance.
     * NOTE: Instance treated as worker if any task queue or tao scheduler process is active.
     *
     * @return bool
     */
    protected function isWorker(): bool
    {
        exec('ps -ef |egrep \'.+index.php\soat\'', $output);
        $taskQueueProcesses = preg_grep('/RunWorker/', $output);
        $schedulerProcess = preg_grep('/JobRunner/', $output);

        return !empty($taskQueueProcesses) || !empty($schedulerProcess);
    }

    /**
     * @return bool
     */
    protected function isAws(): bool
    {
        //this is an assumption.
        //Todo: check if there is another way to check if we are in the AWS environment.
        return $this->getServiceLocator()->has('generis/awsClient');
    }
}
