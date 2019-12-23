<?php
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\tao\helpers\Template;

$report = get_data('report');
$link = get_data('support_portal_link');
$service = get_data('service');
$reportsByStatus = get_data('reports_by_status');

$configurationReports = $reportsByStatus[__('TAO Configuration')];
unset($reportsByStatus[__('TAO Configuration')]);

$configurationValuesReports = $reportsByStatus[__('Configuration Values')];
unset($reportsByStatus[__('Configuration Values')]);

$healthReports = $reportsByStatus[__('Health/Readiness check')];
unset($reportsByStatus[__('Health/Readiness check')]);

$monitoringReports = $reportsByStatus[__('Monitoring / Statistics')];
unset($reportsByStatus[__('Monitoring / Statistics')]);

?>

<link rel="stylesheet" href="<?= Template::css('systemstatus.css') ?>" />

<div class="data-container-wrapper flex-container-full" id="system-status-report">
    <div class="grid-container">
        <div class="grid-row">
            <div class="col-10">
                <h1>Welcome to TAO's status page hub</h1>
            </div>
            <div class="col-2">
                <?php if ($link): ?>
                <a href="<?= $link ?>" target="_blank" class="btn-info large support_portal_link"><span class="icon-save"></span> Visit Support Portal</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-12">
                <div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-0 leaf tao-scope">
                    <span class="icon-<?= mb_strtolower($report->getType()) ?> leaf-icon"></span>
                    <span class="formatted-feedback-message"> <?= $report->getMessage() ?></span>
                </div>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-6">
                <h2 class="section_header"><?= __('TAO Configuration') ?></h2>
                <?php foreach($configurationReports as $report): ?>
                    <?= $service->getCheck($report->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($report) ?>
                <?php endforeach; ?>
            </div>
            <div class="col-6">
                <h2 class="section_header"><?= __('Configuration Values') ?></h2>
                <?php foreach($configurationValuesReports as $report): ?>
                    <?= $service->getCheck($report->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($report) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-12">
                <h2 class="section_header"><?= __('Monitoring / Statistics') ?></h2>
                <?php foreach($monitoringReports as $report): ?>
                    <?= $service->getCheck($report->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($report) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-6">
                <h2 class="section_header"><?= __('Health/Readiness check') ?></h2>
                <?php foreach($healthReports as $report): ?>
                    <?= $service->getCheck($report->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($report) ?>
                <?php endforeach; ?>
            </div>
            <div class="col-6">
                <?php foreach ($reportsByStatus as $status => $reports): ?>
                    <h2 class="section_header"><?= $status ?></h2>
                    <?php foreach($reports as $report): ?>
                        <?= $service->getCheck($report->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($report) ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <div>
</div>
