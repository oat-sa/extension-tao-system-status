<?php
use oat\tao\helpers\Template;
$deliveryUri = get_data("delivery_id");
?>
<div class="data-container-wrapper flex-container-full">
    <h2>Welcome to TAO's status page hub</h2>
    <div class="grid-row">
        <div class="col-12">
            <?php echo tao_helpers_report_Rendering::render(get_data('reports')); ?>
        </div>
    </div>
</div>
