<?php
use oat\tao\helpers\Template;
$report = get_data('reports');
$link = get_data('support_portal_link');
$childRepors = $report->getChildren();
?>
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
                <?php foreach($childRepors as $childReport): ?>
                <div class="feedback-<?= mb_strtolower($childReport->getType()) ?> feedback-nesting-1 leaf tao-scope">
                    <div class="icon-<?= mb_strtolower($childReport->getType()) ?>"></div>
                    <?= $childReport->getMessage() ?>
                    <span style="float: right;">
                        <span class="icon-help r tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
                        <span class="tooltip-content" ><?= $childReport->getData()['details'] ?></span>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <div>
</div>
