<?php
use oat\tao\helpers\Template;
?>

<link rel="stylesheet" href="<?= Template::css('systemstatus.css') ?>" />

<div>
    <div class="status-report-header-container">
        <h1 class="status-report-header"><?= __("Welcome to TAO's status page hub") ?></h1>
        <?php if ($support_portal_link): ?>
            <a href="<?= $support_portal_link ?>" target="_blank" class="btn-info large support_portal_link"><span class="icon-save"></span> <?= __("Visit Support Portal") ?></a>
        <?php endif; ?>
    </div>
    <dev class="status-report-container" id="system-status-report"></div>
    <div class="system-status__charts-container"></div>
</div>
