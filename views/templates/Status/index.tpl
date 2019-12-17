<?php
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\tao\helpers\Template;

$report = get_data('report');
$link = get_data('support_portal_link');
$service = get_data('service');
$childReports = get_data('reports_by_status');

?>

<link rel="stylesheet" href="<?= Template::css('systemstatus.css') ?>" />

<div class="data-container-wrapper flex-container-full" id="system-status-report">
    <div class="grid-container">
        <div class="grid-row">
            <div class="col-9">
                <h1>Welcome to TAO's status page hub</h1>
            </div>
            <div class="col-3">
                <?php if ($link): ?>
                <a href="<?= $link ?>" target="_blank" class="btn-info large"><span class="icon-save"></span> Visit Support Portal</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-9">
                <div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-0 leaf tao-scope">
                    <span class="icon-<?= mb_strtolower($report->getType()) ?> leaf-icon"></span>
                    <span class="formatted-feedback-message"> <?= $report->getMessage() ?></span>
                </div>
                <?php foreach($childReports as $childReportCategory => $categoryReports): ?>
                    <h2><?= $childReportCategory ?></h2>
                    <?php foreach($categoryReports as $childReport): ?>
                        <?= $service->getCheck($childReport->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($childReport) ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <div>
</div>
