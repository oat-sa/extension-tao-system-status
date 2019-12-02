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
use oat\oatbox\service\ServiceManagerAwareTrait;

/**
 * class AbstractCheck
 * @package oat\taoSystemStatus\test\model\Check
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractCheck implements CheckInterface
{
    use ServiceManagerAwareTrait;

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

    abstract public function __invoke($params = []): Report;

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
        $report->setData($data);
        return $report;
    }

    /**
     * @inheritdoc
     */
    abstract public function isActive(): bool;
}
