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
use oat\tao\model\search\SearchProxy;
use oat\tao\elasticsearch\ElasticSearch;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\taoAdvancedSearch\model\Index\Report\IndexSummarizer;

class AdvancedSearchIndexesCheck extends AbstractAdvancedSearchCheck
{
    public function getDetails(): string
    {
        return __('Are indexes populated');
    }

    protected function doCheck(): common_report_Report
    {
        if (!$this->getAdvancedSearchChecker()->isEnabled()) {
            return Report::createError('Advanced search not enabled');
        }

        $advancedSearch = $this->getSearchProxy()->getAdvancedSearch();

        if (!$advancedSearch instanceof ElasticSearch || !$advancedSearch->ping()) {
            return Report::createError('Advanced search unavailable');
        }

        try {
            $reports = [];
            $summary = $this->getIndexSummarizer()->summarize();

            foreach ($summary as $data) {
                if (!$data['totalInDb']) {
                    continue;
                }

                if ($data['percentageIndexed'] !== 100.0) {
                    $reports[] = new Report(
                        $this->getTypeByPercentage($data['percentageIndexed']),
                        sprintf(
                            '%s: %d/%d (%s%%)',
                            $data['index'],
                            $data['totalIndexed'],
                            $data['totalInDb'],
                            $data['percentageIndexed']
                        )
                    );
                }
            }

            if (!empty($reports)) {
                if (count($reports) === count($summary)) {
                    return Report::createError('No', null, $reports);
                }

                return Report::createWarning('Some indexes where not populated', null, $reports);
            }

            return Report::createSuccess('Yes');
        } catch (Throwable $exception) {
            return Report::createError('Unexpected error');
        }
    }

    protected function getTemplate() : string
    {
        return Template::getTemplate('Reports/advancedSearchIndexesReport.tpl', 'taoSystemStatus');
    }

    private function getTypeByPercentage(float $percentage): string
    {
        if ($percentage === 100.0) {
            return Report::TYPE_SUCCESS;
        }

        if ($percentage < 98.0) {
            return Report::TYPE_ERROR;
        }

        return Report::TYPE_WARNING;
    }

    private function getAdvancedSearchChecker(): AdvancedSearchChecker
    {
        return $this->getContainer()->get(AdvancedSearchChecker::class);
    }

    private function getSearchProxy(): SearchProxy
    {
        return $this->getContainer()->get(SearchProxy::SERVICE_ID);
    }

    private function getIndexSummarizer(): IndexSummarizer
    {
        return $this->getContainer()->get(IndexSummarizer::class);
    }
}
