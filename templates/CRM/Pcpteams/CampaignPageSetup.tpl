{if $form.pcp_team_type}
  <div class="crm-section crm-pcp-team-type-section clearfix">
    <div class="label col-xs-12 col-sm-6"><h3>{$form.pcp_team_type.label}</h3></div>
    <div class="content col-xs-12 col-sm-6">{$form.pcp_team_type.html}</div>
    <div class="clear"></div>
  </div>
{/if}
{if $form.pcp_team_id}
  <div class="crm-section crm-pcp-team-name-section clearfix">
    <div class="label col-xs-12 col-sm-6"><h3>{$form.pcp_team_id.label}</h3></div>
    <div class="content col-xs-12 col-sm-6">
      {$form.pcp_team_id.html|crmAddClass:'form-control'}
      {if not $form.pcp_team_id.frozen}
        <div class="description">{ts}Leave this field empty if you do not want to join a team.{/ts}</div>
      {/if}
    </div>
    <div class="clear"></div>
  </div>
{/if}
