<?php
use oat\taoSystemStatus\model\Check\CheckInterface;
use oat\tao\helpers\Template;

$report = get_data('reports');
$link = get_data('support_portal_link');
$service = get_data('service');
$childReports = $report->getChildren();

?>

<link rel="stylesheet" href="<?= Template::css('preview.css') ?>" />

<div class="data-container-wrapper flex-container-full">
    <div class="grid-container">
        <div class="grid-row">
            <div class="col-9">
                <h1>Welcome to TAO's status page hub</h1>
            </div>
            <div class="col-3">
                <?php if ($link): ?>
                <a href="<?= $link ?>" target="_blank" class="form-submitter btn-info large"><span class="icon-save"></span> Visit Support Portal</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-9">
                <div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-0 leaf tao-scope">
                    <span class="icon-<?= mb_strtolower($report->getType()) ?> leaf-icon"></span>
                    <?= $report->getMessage() ?>
                </div>
                <h2>Current Status</h2>
                <?php foreach($childReports as $childReport): ?>
                <?= $service->getCheck($childReport->getData()[CheckInterface::PARAM_CHECK_ID])->renderReport($childReport) ?>
                <?php endforeach; ?>
            </div>
        </div>
    <div>
</div>
