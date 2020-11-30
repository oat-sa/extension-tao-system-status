<?php
declare(strict_types=1);

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

namespace oat\taoSystemStatus\model\Alert;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use common_report_Report as Report;

/**
 * Class OpsgenieAlertService
 * @package oat\taoSystemStatus\model\Alert
 */
class OpsgenieAlertService extends AlertService
{
    const SERVICE_ID = 'taoSystemStatus/AlertService';

    const OPTION_API_KEY = 'api_key';

    const OPSGENIE_API_URI = 'https://api.eu.opsgenie.com';
    const OPSGENIE_API_VERSION = 'v2';

    private const RESPONDERS = [
        [
            'name' => 'Alert Response Team',
            'type' => 'team',
        ]
    ];

    /**
     * @param Alert $alert
     * @return Report
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createAlert(Alert $alert)
    {
        $report = new Report(Report::TYPE_SUCCESS);

        try {
            $request = new Request('POST', 'alerts', $this->getHeaders(), $this->getBody($alert));
            $response = $this->getClient()->send($request);
            $report->setMessage((string) $response->getBody());
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $report->setMessage((string) $response->getBody());
            $report->setType(Report::TYPE_ERROR);
        } catch (\Error $e) {
            $report->setMessage($e->getMessage());
            $report->setType(Report::TYPE_ERROR);
        }
        return $report;
    }

    /**
     * @param Alert $alert
     * @return string
     */
    private function getBody(Alert $alert)
    {
        $body = [
            'message' => $alert->getMessage(),
            'description' => $alert->getDescription(),
            'responders' => self::RESPONDERS,
        ];
        return \GuzzleHttp\json_encode($body);
    }

    /**
     * @return string
     */
    private function getApiKey(): string
    {
        return $this->getOption(self::OPTION_API_KEY);
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'GenieKey ' . $this->getApiKey(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return new Client([
            'base_uri' => self::OPSGENIE_API_URI . '/'. self::OPSGENIE_API_VERSION . '/',
        ]);
    }
}