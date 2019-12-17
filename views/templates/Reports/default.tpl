<?php
$report = get_data('report');
?>
<div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-1 leaf tao-scope">
    <div class="icon-<?= mb_strtolower($report->getType()) ?>"></div>
    <span class="formatted-feedback-message"><?=$report->getMessage()?></span>
    <?php if (isset($report->getData()['details'])): ?>
    <span style="float: right;">
        <span class="icon-help r tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
        <span class="tooltip-content" ><?= $report->getData()['details'] ?></span>
    </span>
    <?php endif; ?>
</div>
