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

/**
 * class AbstractCheck
 * @package oat\taoSystemStatus\test\model\Check
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractCheck implements CheckInterface
{
    /** @var string  */
    private $type;
    /** @var array  */
    private $params;
    /** @var string */
    private $category;
    /** @var string */
    private $details;

    /**
     * AbstractCheck constructor.
     * @param $type
     * @param $params
     */
    public function __construct(string $type, array $params = [])
    {
        $this->type = $type;
        $this->params = $params;
        $this->category = $params[static::PARAM_CATEGORY] ?? static::DEFAULT_CATEGORY;
        $this->details = $params[static::PARAM_DETAILS] ?? '';
    }

    abstract public function __invoke($params): Report;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @inheritdoc
     */
    public function getDetails(): string
    {
        return $this->details;
    }

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
     */
    abstract public function isActive(): bool;
}