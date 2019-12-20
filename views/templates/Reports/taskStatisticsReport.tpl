<?php
$report = get_data('task-report');
$statistics = get_data('task-statistics');
?>

<div class="js-report">
    <h3>Task Queue statistics</h3>
    <div id="taskqueue_log_report-statistics">
        <select class="js-tasks-statistics-interval" data-has-search="false">
            <option value="P1D"><?= __('Last Day') ?></option>
            <option value="P1W"><?= __('Last Week') ?></option>
            <option value="P1M"><?= __('Last Month') ?></option>
        </select>
        <em>Note: statistics represented in UTC timezone.</em>
        <div class="tasks-graph-container js-tasks-graph-container">
            <div class="js-tasks-graph" data-statistics='<?= $statistics ?>'></div>
        </div>
    </div>
</div>
