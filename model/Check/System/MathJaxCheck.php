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

namespace oat\taoSystemStatus\model\Check\System;

use common_report_Report as Report;
use common_ext_ExtensionException;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class MathJaxCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class MathJaxCheck extends AbstractCheck
{
    private const MATH_JAZ_FOLDER_PREFIX = 'views/js/mathjax';

    /**
     * @param array $params
     * @return Report
     * @throws common_ext_ExtensionException
     */
    public function __invoke($params = []): Report
    {
        $report = $this->checkMathJax();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
       return true;
    }

    /**
     * @todo Should be changed to type instance after checking
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_SYSTEM;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return __('System configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('Check if MathJax correctly configured');
    }

    /**
     * @return Report
     * @throws common_ext_ExtensionException
     */
    private function checkMathJax() : Report
    {
        $qtiItemDir = $this->getExtensionsManagerService()->getExtensionById('taoQtiItem')->getDir();
        if (!$this->checkDirIsEmpty($qtiItemDir.self::MATH_JAZ_FOLDER_PREFIX)) {
            return new Report(Report::TYPE_WARNING, __('MathJax folder is empty.'));
        }

        if (!$this->isMathJaxJsExists($qtiItemDir.self::MATH_JAZ_FOLDER_PREFIX.'/MathJax.js')) {
            return new Report(Report::TYPE_WARNING, __('MathJax.js is not exist.'));
        }

        return new Report(Report::TYPE_SUCCESS, __('MathJax correctly configured.'));
    }

    /**
     * @param $dir
     * @return bool
     */
    private function checkDirIsEmpty($dir) : bool
    {
        if (!is_readable($dir)) {
            return false;
        }
        return count(scandir($dir)) > 1;
    }

    /**
     * @param $filename
     * @return bool
     */
    private function isMathJaxJsExists($filename) : bool
    {
        return file_exists($filename);
    }
}
