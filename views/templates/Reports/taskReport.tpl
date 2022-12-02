<?php
use oat\taoSystemStatus\model\Check\System\TaskQueueFailsCheck;

$reports = get_data('reports');
?>

<h3><?= __('Last %d failed tasks', count($reports)) ?></h3>
<table class="matrix taskqueue_log_table">
    <thead>
        <tr>
            <th><?= __('Label') ?></th>
            <th><?= __('Created At') ?></th>
            <th><?= __('Details') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($reports as $report): ?>
            <tr class="js-report taskqueue_log_report">
                <th><?= $report['task-report']->getData()[TaskQueueFailsCheck::TASK_LABEL] ?></th>
                <td><?= $report['task-report']->getData()[TaskQueueFailsCheck::TASK_REPORT_TIME] ?></td>
                <td>
                    <button class="btn-info small js-feedback-details-button" type="button">Details</button>
                    <div class="js-feedback-details feedback-details">
                        <table class="matrix">
                            <thead>
                                <tr>
                                    <td>
                                        <?= __('Message') ?>
                                    </td>
                                    <td>
                                        <?= __('Data') ?>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($report['task-report-flat'] as $taskReport): ?>
                                <tr>
                                    <td>
                                        <div class="feedback-<?= mb_strtolower($taskReport->getType()) ?> small">
                                            <div class="icon-<?= mb_strtolower($taskReport->getType()) ?>"></div><span class="formatted-feedback-message"><?= $taskReport->getMessage() ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($taskReport->getData()): ?>
                                        <pre><?= json_encode($taskReport->getData(), JSON_PRETTY_PRINT) ?></pre>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

