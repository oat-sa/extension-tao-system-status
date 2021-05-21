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

namespace oat\taoSystemStatus\model\Check\System;

use oat\oatbox\reporting\Report;
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\oatbox\log\loggerawaretrait;
use DateTime;

/**
 * Class CertificateCheck
 * @package oat\taoSystemStatus\model\Check\System
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class CertificateCheck extends AbstractCheck
{
    use LoggerAwareTrait;

    const WARNING_DAYS = 14;
    const ERROR_DAYS = 7;
    /**
     * @inheritdoc
     */
    protected function doCheck(): Report
    {
        $certInfo = $this->getCertInfo();

        $now = new DateTime();
        $validTo = new DateTime('@' . $certInfo['validTo_time_t']);;
        $diffDays = $validTo->diff($now)->format("%a");
        $data = $validTo->format('Y-m-d H:i:s');

        if ($diffDays < self::ERROR_DAYS) {
            return Report::createError($data);
        } elseif ($diffDays < self::WARNING_DAYS) {
            return Report::createWarning($data);
        }
        return Report::createSuccess($data);
    }

    /**
     * Get certification info based on RootUrl
     * @return array
     */
    private function getCertInfo() : array
    {
        $url = \tao_helpers_Uri::getRootUrl();

        $originalParse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client(
            sprintf('ssl://%s:443', $originalParse),
            $errno,
            $errStr,
            30,
            STREAM_CLIENT_CONNECT,
            $get
        );
        $cert = stream_context_get_params($read);
        return openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
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
