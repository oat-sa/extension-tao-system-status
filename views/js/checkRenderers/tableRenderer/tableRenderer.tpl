<div class="system-status-table-wrapper {{#if small}}system-status-table-wrapper--small{{/if}}">
    <div class="system-status-table-title">
        {{category}}
    </div>
    <div class="system-status-table-container">
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
                    {{#if addDetailsCell}}
                        <td class="system-status-table-details-row">
                            {{#if detailsButton}}
                              <button class="btn-info small details-button" type="button"
                                  data-report="{{reportData}}">{{__ "View Report"}}</button>
                            {{/if}}
                        </td>
                    {{/if}}
                </tr>
                {{/each}}
            </tbody>
        </table>
    </div>
</div>
