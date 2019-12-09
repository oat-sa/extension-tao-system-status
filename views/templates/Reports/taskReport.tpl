<?php
$reports = get_data('reports');
?>

<div class="taskqueue_log_report">

</div>
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
