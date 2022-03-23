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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\model\Check\System;

use DateTime;
use common_report_Report as Report;
use oat\taoSystemStatus\model\Check\AbstractCheck;

/**
 * Class CertificateCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 * @deprecated This check is not used anymore and will be deleted in the next release
 */
class CertificateCheck extends AbstractCheck
{

    private const WARNING_DAYS = 14;
    private const ERROR_DAYS = 7;

    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $certInfo = $this->getCertInfo();

        if (!$certInfo) {
            return Report::createWarning(__('Cannot get cert Info'));
        }

        $now = new DateTime();
        $validTo = new DateTime('@' . $certInfo['validTo_time_t']);;
        $diffDays = $validTo->diff($now)->format("%a");
        $date = $validTo->format('Y-m-d H:i:s');

        $report = Report::createSuccess($date);
        if ($diffDays < self::ERROR_DAYS) {
            $report = Report::createError($date);
        } elseif ($diffDays < self::WARNING_DAYS) {
            $report = Report::createWarning($date);
        }
        return $report;
    }

    /**
     * @return array|null
     */
    private function getCertInfo() : ?array
    {
        $certInfo = null;
        if (!$url = \tao_helpers_Uri::getRootUrl()) {
            return null;
        }
        $originalParse = parse_url($url);
        if (isset($originalParse['scheme']) && $originalParse['scheme'] !== 'https') {
            return null;
        }
        $get = stream_context_create(
            [
                "ssl" => [
                    "capture_peer_cert" => TRUE
                ]
            ]
        );
        $read = stream_socket_client(
            sprintf(
                'ssl://%s:443',
                $originalParse['host']
            ),
            $errno,
            $errStr,
            30,
            STREAM_CLIENT_CONNECT,
            $get
        );
        if ($read) {
            $cert = stream_context_get_params($read);
            if (isset($cert['options']['ssl']['peer_certificate'])) {
                $certInfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
            }
        }
        return $certInfo;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return false;
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
        return __('Health/Readiness check');
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return __('The certificate will be expired at');
    }
}
