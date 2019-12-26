<?php
use oat\tao\helpers\Template;
?>

<link rel="stylesheet" href="<?= Template::css('systemstatus.css') ?>" />

<div class="status-report-container" id="system-status-report">
    <h1 class="status-report-header"><?= __("Welcome to TAO's status page hub") ?></h1>
    <div class="status-report-configuration-tables" id="system-status-configuration-tables">
    </div>
</div>
