<?php
    $details = get_data('details');
    $value = get_data('report_value');
?>

<div class='system_status_info_block'>
    <span class='system_status_info_block__label'><?= $details ?> </span>
    <br>
    <span class='system_status_info_block__value'><?= $value ?></span>
</div>
