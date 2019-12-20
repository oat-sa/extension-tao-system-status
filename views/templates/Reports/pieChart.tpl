<?php
$label = get_data('label');
$val = get_data('val');
?>

<div class="system_check_progress_circle">
    <span><?= $label ?></span>
    <div class="progress-circle <?php if ($val > 50): ?> over50 <?php endif; ?> p<?= $val ?>">
        <span><?= $val ?>%</span>
        <div class="left-half-clipper">
            <div class="first50-bar"></div>
            <div class="value-bar"></div>
        </div>
    </div>
</div>

