<?php
$report = get_data('report');
?>
<div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-1 small">
    <div class="icon-<?= mb_strtolower($report->getType()) ?>"></div>
    <?php if (isset($report->getData()['details'])): ?>
    <b><?= $report->getData()['details'] ?>:</b><br>
    <?php endif; ?>
<span class="formatted-feedback-message"><?=$report->getMessage()?></span>
</div>
