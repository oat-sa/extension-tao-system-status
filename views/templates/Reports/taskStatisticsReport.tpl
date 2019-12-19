<?php
$report = get_data('task-report');
$statistics = get_data('task-statistics');
?>

<div class="js-report">
    <div class="feedback-<?= mb_strtolower($report->getType()) ?> feedback-nesting-1 leaf tao-scope">
        <div class="icon-<?= mb_strtolower($report->getType()) ?>"></div>
        <span class="formatted-feedback-message"><?= $report->getMessage() ?></span>
        <?php if (isset($report->getData()['details'])): ?>
        <span style="float: right;">
            <span class="icon-help r tooltipstered" data-tooltip="~ .tooltip-content:first" data-tooltip-theme="info"></span>
            <span class="tooltip-content" ><?= $report->getData()['details'] ?></span>
        </span>
        <?php endif; ?>
        <span style="float: right;">
              <button class="btn-info small taskqueue_log_report-statistics-button" type="button">Details</button>
        </span>
    </div>

    <div class="js-tasks-statistics-modal modal">
        <h2>Task Queue statistics</h2>
        <em>Note: statistics represented in UTC timezone.</em><br>
        <div id="taskqueue_log_report-statistics">
            <select class="js-tasks-statistics-interval" data-has-search="false">
                <option value="P1D"><?= __('Last Day') ?></option>
                <option value="P1W"><?= __('Last Week') ?></option>
                <option value="P1M"><?= __('Last Month') ?></option>
            </select>
            <div class="tasks-graph-container js-tasks-graph-container">
                <div class="js-tasks-graph" data-statistics='<?= $statistics ?>'></div>
            </div>
        </div>
    </div>
</div>
