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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\taoSystemStatus\model\Check\System\AdvancedSearch;

use Throwable;
use common_report_Report;
use oat\tao\helpers\Template;
use oat\oatbox\reporting\Report;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\taoAdvancedSearch\model\Index\Report\IndexSummarizer;

class AdvancedSearchIndexationCheck extends AbstractAdvancedSearchCheck
{
    public const PARAM_ALLOWABLE_PERCENTAGE = 'allowablePercentage';
    public const PARAM_BLACKLISTED_INDEXES = 'blacklistedIndexes';

    /** @var float */
    private $allowablePercentage;

    /** @var array */
    private $blacklistedIndexes;

    /** @var Report[] */
    private $reports = [];

    /** @var Report[] */
    private $errorReports = [];

    public function __construct(array $params = [])
    {
        $this->allowablePercentage = (float) ($params[self::PARAM_ALLOWABLE_PERCENTAGE] ?? 80);
        $this->blacklistedIndexes = $params[self::PARAM_BLACKLISTED_INDEXES] ?? [];

        parent::__construct($params);
    }

    public function getDetails(): string
    {
        return __('Indexes population');
    }

    protected function getTemplate() : string
    {
        return Template::getTemplate('Reports/advancedSearchIndexesReport.tpl', 'taoSystemStatus');
    }

    protected function doCheck(): common_report_Report
    {
        $advancedSearchChecker = $this->getAdvancedSearchChecker();

        if (!$advancedSearchChecker->isEnabled()) {
            return Report::createError('Advanced search disabled');
        }

        if (!$advancedSearchChecker->ping()) {
            return Report::createError('Advanced search unavailable');
        }

        try {
            $processedIndexesCount = 0;

            foreach ($this->getIndexSummarizer()->summarize() as $data) {
                if (
                    !$data['totalInDb']
                    || $data['percentageIndexed'] === 100.0
                    || in_array($data['index'], $this->blacklistedIndexes, true)
                ) {
                    continue;
                }

                $this->addReport($this->createReport($data));
                ++$processedIndexesCount;
            }

            return $this->buildFinalReport($processedIndexesCount);
        } catch (Throwable $exception) {
            return Report::createError('Unexpected error');
        }
    }

    private function createReport($data): Report
    {
        $type = $this->getTypeByPercentage($data['percentageIndexed']);
        $message = __(
            'Index "%s": %d/%d (%s%%)',
            $data['index'],
            $data['totalIndexed'],
            $data['totalInDb'],
            $data['percentageIndexed']
        );

        return new Report($type, $message);
    }

    private function addReport(Report $report): void
    {
        $this->reports[] = $report;

        if ($report->getType() === Report::TYPE_ERROR) {
            $this->errorReports[] = $report;
        }

        $this->{'log' . $report->getType()}(
            sprintf(
                '%s. Minimum allowed percentage is: %s%%',
                $report->getMessage(),
                $this->allowablePercentage
            )
        );
    }

    private function getTypeByPercentage(float $percentage): string
    {
        if ($percentage === 100.0) {
            return Report::TYPE_SUCCESS;
        }

        if ($percentage < $this->allowablePercentage) {
            return Report::TYPE_ERROR;
        }

        return Report::TYPE_WARNING;
    }

    private function buildFinalReport(int $processedIndexesCount): Report
    {
        if (count($this->errorReports) === $processedIndexesCount) {
            return Report::createError('Indexes are not populated', null, $this->errorReports);
        }

        if (!empty($this->reports)) {
            return Report::createWarning('Some indexes are not populated', null, $this->reports);
        }

        return Report::createSuccess('Indexes are populated');
    }

    private function getAdvancedSearchChecker(): AdvancedSearchChecker
    {
        return $this->getContainer()->get(AdvancedSearchChecker::class);
    }

    private function getIndexSummarizer(): IndexSummarizer
    {
        return $this->getContainer()->get(IndexSummarizer::class);
    }
}
