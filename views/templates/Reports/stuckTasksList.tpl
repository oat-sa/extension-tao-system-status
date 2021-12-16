<?php
use oat\taoSystemStatus\model\Check\System\StuckTasksCheck;

$reports = get_data('reports');
?>

<div class="system_status_stuck_tasks_block">
    <h2><?= __('Last %d stuck tasks', count($reports)) ?></h2>
    <table class="matrix taskqueue_log_table">
        <thead>
        <tr>
            <th><?= __('Task ID') ?></th>
            <th><?= __('Label') ?></th>
            <th><?= __('Status') ?></th>
            <th><?= __('Last activity') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($reports as $report): ?>
        <tr class="js-report taskqueue_log_report">
            <th><?= $report['task-report']->getData()[StuckTasksCheck::OPTION_TASK_ID] ?></th>
            <th><?= $report['task-report']->getData()[StuckTasksCheck::OPTION_LABEL] ?></th>
            <td><?= $report['task-report']->getData()[StuckTasksCheck::OPTION_STATUS] ?></td>
            <td><?= $report['task-report']->getData()[StuckTasksCheck::OPTION_UPDATED] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

