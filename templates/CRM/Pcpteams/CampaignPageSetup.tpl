{if $form.pcp_team_type}
  <div class="crm-section crm-pcp-team-type-section">
    <div class="label">{$form.pcp_team_type.label}</div>
    <div class="content">{$form.pcp_team_type.html}</div>
    <div class="clear"></div>
  </div>
{/if}
{if $form.pcp_team_id}
  <div class="crm-section crm-pcp-team-name-section">
    <div class="label">{$form.pcp_team_id.label}</div>
    <div class="content">
      {$form.pcp_team_id.html}
      {if not $form.pcp_team_id.frozen}
        <div class="description">{ts}Leave this field empty if you do not want to join a team.{/ts}</div>
      {/if}
    </div>
    <div class="clear"></div>
  </div>
{/if}
