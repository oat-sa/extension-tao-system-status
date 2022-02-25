<?php
$report = get_data('report');
?>
<div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-1 small">
    <div class="icon-<?= mb_strtolower($report->getType()) ?>"></div>

    <?php if (isset($report->getData()['details'])): ?>
    <b><?= $report->getData()['details'] ?>:</b><br>
    <?php endif; ?>

    <span class="formatted-feedback-message"><?= $report->getMessage() ?></span>

    <?php if (!empty($report->getChildren())): ?>

    <ul class="formatted-feedback-list">
        <?php foreach ($report->getChildren() as $child): ?>
        <li class="formatted-feedback-list-element">
            <div class="formatted-feedback-list-icon icon-<?= mb_strtolower($child->getType()) ?>"></div>
            <span class="formatted-feedback-message"><?= $child->getMessage() ?></span>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php endif; ?>
</div>
