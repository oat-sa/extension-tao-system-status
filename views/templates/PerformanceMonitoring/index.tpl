<?php
use oat\tao\helpers\Template;
?>

<div class="data-container-wrapper flex-container-full">
    <div class="grid-container">
        <div class="grid-row">
            <div class="col-12">
                <h1>Performance Monitoring</h1>
            </div>
        </div>
        <div class="grid-row">
            <div class="col-12">
                <h2 class="section_header"><?= __('Test session statistics') ?></h2>
                <div class="js-report">
                    <div>
                        <select class="js-execution-statistics-interval" data-has-search="false">
                            <option value="PT1H"><?= __('Last Hour') ?></option>
                            <option value="P1D"><?= __('Last Day') ?></option>
                            <option value="P1M"><?= __('Last Month') ?></option>
                        </select>
                        <em>Note: statistics represented in UTC timezone.</em>
                        <div class="tasks-graph-container js-execution-statistics-graph-container">
                            <div class="js-execution-statistics-graph"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
