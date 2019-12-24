<div class="system-status__statistics-chart-container">
    <div class="system-status-table-title">
        {{__ "Task Queue Statistics"}}
    </div>
    <div class="statistics-chart__interval-select-container">
        <select class="statistics-chart__interval-select" value="{{defaultInterval}}">
            {{#each intervals}}
                <option value="{{value}}">{{label}}</option>
            {{/each}}
        </select>
    </div>
    <div class="js-tasks-graph">
    </div>
</div>
