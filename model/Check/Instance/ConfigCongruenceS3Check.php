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
use oat\taoSystemStatus\model\Check\AbstractCheck;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\log\loggerawaretrait;

/**
 * Class ConfigCongruenceS3Check
 * @package oat\taoSystemStatus\model\Check\Instance
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ConfigCongruenceS3Check extends AbstractCheck
{
    use LoggerAwareTrait;

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params = []): Report
    {
        if (!$this->isActive()) {
            return new Report(Report::TYPE_INFO, 'Check ' . $this->getId() . ' is not active');
        }

        $tmpDir = \tao_helpers_File::createTempDir() . 'migrations' . DIRECTORY_SEPARATOR . 'config';
        $s3Client = $this->getS3Client();
        $adapterOptions = $this->getAdapterOptions();
        $bucket = $adapterOptions['options'][0]['bucket'];
        $s3Client->downloadBucket($tmpDir, $bucket, '/migrations/config');

        if ($this->hashDirectory($tmpDir) === $this->hashDirectory(CONFIG_PATH)) {
            $report = new Report(Report::TYPE_SUCCESS, __('The instance configuration is correct'));
        } else {
            $this->logError('The instance configuration is not match configuration stored on s3');
            $report = new Report(Report::TYPE_ERROR, __('The instance configuration is not match configuration stored on s3'));
        }

        return $this->prepareReport($report);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        /** @var FileSystemService $fileSystem */
        $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        if (class_exists('oat\awsTools\AwsFileSystemService') && get_class($fileSystem) === 'oat\awsTools\AwsFileSystemService'){
            $adapterOptions = $this->getAdapterOptions();
            $bucket = $adapterOptions['options'][0]['bucket'];
            $s3Client = $this->getS3Client();
            if ($s3Client->doesObjectExist($bucket, 'migrations/config/generis.conf.php')) {
                return true;
            }
        }
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
        return __('Configuration Files Compliance');
    }

    /**
     * @return \Aws\S3\S3Client
     */
    private function getS3Client()
    {
        $adapterOptions = $this->getAdapterOptions();
        $client = $adapterOptions['options'][0]['client'];

        /** @var \oat\awsTools\AwsClient $awsClient */
        $awsClient = $this->getServiceLocator()->get($client);
        return $awsClient->getS3Client();
    }

    /**
     * @return mixed
     */
    private function getAdapterOptions()
    {
        $fileSystem = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        $filesystemConf = $fileSystem->getOptions();
        if (isset($filesystemConf['dirs']['private'])) {
            $adapterOptions = $filesystemConf['adapters'][$filesystemConf['dirs']['private']];
        } else {
            $adapterOptions = $filesystemConf['adapters']['private'];
        }
        return $adapterOptions;
    }

    /**
     * Generate an MD5 hash string from the contents of a directory.
     *
     * @param string $path
     * @return boolean|string
     */
    private function hashDirectory($path)
    {
        if (!is_dir($path)) {
            return false;
        }

        $files = [];
        $directory = dir($path);

        while (false !== ($file = $directory->read())) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    $files[] = $this->hashDirectory($path . DIRECTORY_SEPARATOR . $file);
                } else {
                    $files[] = md5_file($path . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        $directory->close();
        return md5(implode('', $files));
    }
}
