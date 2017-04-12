{if $form.pcp_team_type}
  <div class="crm-section crm-pcp-team-info-section help">
    <p><strong>If you want to start a team</strong>, first create a team page. Then you (and your friends) can create your individual pages, and add them to the team.</p>
    <p><strong>If you want to join an existing team</strong>, choose "Individual" for the "Type" below, then select the team you'd like you join from the "Team" drop-down list.</p>
  </div>
  <div class="crm-section crm-pcp-team-type-section">
    <div class="label">{$form.pcp_team_type.label}</div>
    <div class="content">
      {$form.pcp_team_type.html}
      <div class="description">{ts}If this page is being used to represent a team, choose "Team", otherwise choose "Individual" to create a page for yourself as a person. If you are a member of a team, choose "Individual" here and then choose the team to join below.{/ts}</div>
    </div>
    <div class="clear"></div>
  </div>
{/if}
{if $form.pcp_team_id}
  <div class="crm-section crm-pcp-team-name-section">
    <div class="label">{$form.pcp_team_id.label}</div>
    <div class="content">
      {$form.pcp_team_id.html}
      {if not $form.pcp_team_id.frozen}
        <div class="description">{ts}Choose the team you'd like to join. Leave this field empty if you do not want to join a team.{/ts}</div>
      {/if}
    </div>
    <div class="clear"></div>
  </div>
{/if}
