<?php
use oat\tao\helpers\Template;
?>

<link rel="stylesheet" href="<?= Template::css('systemstatus.css') ?>" />


<div class="status-report-header-container">
    <h1 class="status-report-header"><?= __("Welcome to TAO's status page hub") ?></h1>
    <a href="http://www.google.com" target="_blank" class="btn-info large support_portal_link"><?= __("Visit Support Portal") ?></a>
    <?php if ($support_portal_link): ?>
      <a href="<?= $support_portal_link ?>" target="_blank" class="btn-info large support_portal_link"><?= __("Visit Support Portal") ?></a>
    <?php endif; ?>
</div>
<div class="status-report-container" id="system-status-report"></div>
<div class="system-status__charts-container"></div>
