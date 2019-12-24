<div class="system-status-table-wrapper">
    <div class="system-status-table-title">
        {{category}}
    </div>
    <table class="system-status-table">
        <thead>
            <tr class="system-status-table-row system-status-table-row--head">
                {{#each columns}}
                    <th>{{this}}</th>
                {{/each}}
            </tr>
        </thead>
        <tbody>
            {{#each data}}
                <tr class="system-status-table-row">
                    {{#if type}}
                        <td class="system-status-table-status-cell">
                            {{#if issuccess}}
                                <div class="status-report-icon--success">
                                    <i class="icon-result-ok" />
                                </div>
                            {{/if}}
                            {{#if isinfo}}
                                <div class="status-report-icon--success">
                                    <i class="icon-result-ok" />
                                </div>
                            {{/if}}
                            {{#if iserror}}
                                <div class="status-report-icon--error">
                                    <i class="icon-result-nok" />
                                </div>
                            {{/if}}
                            {{#if iswarning}}
                                <div class="status-report-icon--warning">
                                    <i class="icon-warning" />
                                </div>
                            {{/if}}
                        </td>
                    {{/if}}
                    {{#each rows}}
                        <td>
                            {{{this}}}
                        </td>
                    {{/each}}
                    {{#if detailsButton}}
                        <th>
                            <button class="system-status-table__details-button" type="button">{{__ "View Report"}}</button>
                        </th>
                    {{/if}}
                </tr>
            {{/each}}
        </tbody>
    </table>
</div>
