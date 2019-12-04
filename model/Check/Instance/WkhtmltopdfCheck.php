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

namespace oat\taoSystemStatus\model\Check\Instance;

use common_report_Report as Report;
use oat\taoBooklet\model\export\PdfBookletExporter;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use common_ext_ExtensionException;

/**
 * Class WkhtmltopdfCheck 
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class WkhtmltopdfCheck extends AbstractCheck
{

    /**
     * Version 12.5 of wkhtmltopdf lib is last stable version
     */
    private const MINIMUM_STABLE_VERSION = '0.12.5';

    /**
     * @param array $params
     * @return Report
     * @throws \common_ext_ExtensionException
     */
    public function __invoke($params = []): Report
    {
        $report = $this->checkWkhtmltopdf();
        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->ifTaoBookletIsInstalled();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_INSTANCE;
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
        return __('Check if wkhtmltopdf lib is installed and correctly configured');
    }

    /**
     * @return Report
     * @throws common_ext_ExtensionException
     */
    private function checkWkhtmltopdf() : Report
    {
        $options = $this->getWkhtmltopdfConfig();
        $guessPath = PdfBookletExporter::guessWhereWkhtmltopdfInstalled();

        $process = shell_exec(escapeshellarg(trim($guessPath)) . ' -V');
        preg_match("/(?:wkhtmltopdf)\s*((?:[0-9]+\.?)+)/i", $process, $matches);

        if (isset($matches[1])) {
            //
            if (version_compare($matches[1], self::MINIMUM_STABLE_VERSION) < 0) {
                return new Report(
                    Report::TYPE_WARNING,
                    __('wkhtmltopdf lib has the wrong version %s. Should %s or more', $matches[1], self::MINIMUM_STABLE_VERSION)
                );
            }
        }

        $bin = $options['binary'];
        if ($guessPath !== $bin) {
            return new Report(
                Report::TYPE_WARNING,
                __('wkhtmltopdf lib not correctly configured in taoBooklet extension. Please, check config/taoBooklet/wkhtmltopdf.conf.php.')
            );
        }

        return new Report(
            Report::TYPE_SUCCESS,
            __('wkhtmltopdf lib correctly configured in taoBooklet extension.')
        );
    }

    /**
     * @return bool
     */
    private function ifTaoBookletIsInstalled() : bool
    {
        $extensionManagerService = $this->getExtensionsManagerService();
        return $extensionManagerService->isInstalled('taoBooklet');
    }

    /**
     * @return array
     * @throws common_ext_ExtensionException
     */
    private function getWkhtmltopdfConfig() : array
    {
        return $this->getExtensionsManagerService()->getExtensionById('taoBooklet')->getConfig('wkhtmltopdf');
    }
}
