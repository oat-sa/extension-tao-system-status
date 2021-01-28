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

namespace oat\taoSystemStatus\model\Check\System\Act;

use common_report_Report as Report;
use oat\taoAct\model\webservices\LeapOdsResultService;
use oat\taoAct\model\webservices\OdsReconciliationService;
use oat\taoAct\model\webservices\OdsResultService;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\taoAct\model\webservices\OdsConnector;
use oat\taoAct\model\webservices\InvalidTokenException;
use common_exception_Error;

/**
 * Class OdsConfigurationCheck
 * @package oat\taoSystemStatus\model\Check\System\Act
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class OdsConfigurationCheck extends AbstractCheck
{

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $report = new Report(Report::TYPE_INFO);

        $report->add($this->checkOdsConnector());
        $report->add($this->checkOdsResultService());
        $report->add($this->checkOdsReconciliation());
        $report->add($this->checkLeapOdsResultService());

        if (count($report->getSuccesses()) === count($report->getChildren())) {
            $report->setType(Report::TYPE_SUCCESS);
        } elseif(count($report->getErrors()) > 0)  {
            $report->setType(Report::TYPE_ERROR);
        } else {
            $report->setType(Report::TYPE_WARNING);
        }

        return $report;
    }

    /**
     * @param Report $report
     * @return Report
     */
    protected function prepareReport(Report $report): Report
    {
        $msg = '';
        /** @var Report $childReport */
        foreach ($report->getIterator() as $childReport) {
            $msg .= $childReport->getMessage() . PHP_EOL;
        }
        $report->setMessage($msg);
        return parent::prepareReport($report);
    }

    /**
     * @return Report
     * @throws InvalidTokenException
     */
    private function checkOdsConnector(): Report
    {
        $service = $this->getOdsConnector();

        $options = $service->getOptions();
        if (
            empty($options[OdsConnector::OPTION_CLIENT_ID]) ||
            empty($options[OdsConnector::OPTION_TOKEN_URL]) ||
            empty($options[OdsConnector::OPTION_CONSUMER_SECRET])
        ) {
            return new Report(Report::TYPE_ERROR, __('Ods Connector service is not configured'));
        }

        //attempt to make an empty 'reconcile' request
        $url = $this->getOdsResultService()->getOption(OdsResultService::OPTION_ODS_URL);
        $url = rtrim($url, '/') . '/about/';
        $response = $service->request($url, [], 'GET');

        if ($response) {
            $code = (string) $response->getStatusCode();
            $body = (string) $response->getBody();
            if (preg_match('/2\d\d/', $code)) {
                $report = new Report(Report::TYPE_SUCCESS, __('Ods Connector service correctly configured'));
            } else {
                $report = new Report(Report::TYPE_ERROR, __('Call to "about" ODS endpoint failed with %s error code', $code));
            }
            $report->setData(['body' => $body]);
        } else {
            $report = new Report(Report::TYPE_ERROR, __('Error during execution of ODS request'));
        }

        return $report;
    }

    /**
     * @return Report
     */
    private function checkOdsResultService(): Report
    {
        $options = $this->getOdsResultService()->getOptions();
        //if url is default
        if ($options[OdsResultService::OPTION_ODS_URL] === 'https://api-dev.act.org/medw/1.0.0.5.5/') {
            return new Report(Report::TYPE_ERROR, __('Ods Result service has default value of `ods_url` option'));
        }

        return new Report(Report::TYPE_SUCCESS, __('Ods Result service correctly configured'));
    }

    /**
     * @return Report
     */
    private function checkOdsReconciliation(): Report
    {
        $options = $this->getOdsReconciliationService()->getOptions();
        //if url is default
        if ($options[OdsReconciliationService::OPTION_RECONCILIATION_REQUEST_URL] === 'https://api-dev.act.org/medw/1.0.0.5.5/reconcile/') {
            return new Report(Report::TYPE_ERROR, __('Ods Reconciliation service has default value of `reconciliation_request_url` option'));
        }

        return new Report(Report::TYPE_SUCCESS, __('Ods Reconciliation service correctly configured'));
    }

    /**
     * @return Report
     */
    private function checkLeapOdsResultService(): Report
    {
        $options = $this->getLeapOdsResultService()->getOptions();
        //if url is default
        if ($options[LeapOdsResultService::OPTION_ODS_URL] === 'https://api5-dev.act.org/eventsapi_v1/') {
            return new Report(Report::TYPE_ERROR, __('LEAP Ods result service has default value of `ods_url` option'));
        }

        return new Report(Report::TYPE_SUCCESS, __('LEAP Ods result service correctly configured'));
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getExtensionsManagerService()->isInstalled('taoAct');
    }

    /**
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
        return __('TAO Configuration');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('ODS/LEAP services configuration');
    }

    /**
     * @return LeapOdsResultService
     */
    private function getLeapOdsResultService() : LeapOdsResultService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(LeapOdsResultService::SERVICE_ID);
    }

    /**
     * @return OdsReconciliationService
     */
    private function getOdsReconciliationService() : OdsReconciliationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(OdsReconciliationService::SERVICE_ID);
    }

    /**
     * @return OdsResultService
     */
    private function getOdsResultService() : OdsResultService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(OdsResultService::SERVICE_ID);
    }

    /**
     * @return OdsConnector
     */
    private function getOdsConnector() : OdsConnector
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(OdsConnector::SERVICE_ID);
    }

}
