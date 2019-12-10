<?php
$reports = get_data('reports');
$report = get_data('task-report');
?>

<div class="taskqueue_log_report js-report">
    <div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-1 leaf tao-scope">
        <div class="icon-<?= mb_strtolower($report->getType()) ?>"></div>
        <?= $report->getMessage() ?>
        <?php if (isset($report->getData()['details'])): ?>
        <span style="float: right;">
            <span class="icon-help r tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
            <span class="tooltip-content" ><?= $report->getData()['details'] ?></span>
        </span>
        <?php endif; ?>
        <span style="float: right;">
              <button class="btn-info small js-feedback-details-button" type="button">Details</button>
        </span>
    </div>
    <div class="js-feedback-details feedback-details">
        <table class="matrix">
            <thead>
            <tr>
                <td>
                    Message
                </td>
                <td>
                    Data
                </td>
            </tr>
            </thead>
            <tbody>
            <?php foreach($reports as $report): ?>
            <tr>
                <td>
                    <div class="feedback-<?= mb_strtolower($report->getType()) ?> small">
                        <div class="icon-<?= mb_strtolower($report->getType()) ?>"></div><?= $report->getMessage() ?>
                    </div>
                </td>
                <td>
                    <?php if($report->getData()): ?>
                    <pre>
        <?= json_encode($report->getData(), JSON_PRETTY_PRINT) ?>
                    </pre>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
