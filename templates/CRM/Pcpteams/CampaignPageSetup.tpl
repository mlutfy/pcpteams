<table class="form-layout-compressed" id="crm-contribution-campaign-form-block-pcp-team">
{if $form.pcp_team_type}
  <tr  class="crm-contribution-form-block-pcp_team_type">
      <td class="label">{$form.pcp_team_type.label}</td>
      <td>{$form.pcp_team_type.html}</td>
  </tr>
{/if}
{if $form.pcp_team_type_description}
  <tr  class="crm-contribution-form-block-pcp_team_type_description">
      <td class="label">{$form.pcp_team_type_description.label}</td>
      <td>{$form.pcp_team_type_description.html}</td>
  </tr>
{/if}
{if $form.pcp_team_id}
  <tr  class="crm-contribution-form-block-pcp_team_name" id="pcp_team_id_wrapper">
      <td class="label">{$form.pcp_team_id.label}</td>
      <td>{$form.pcp_team_id.html}
        {if not $form.pcp_team_id.frozen}
          <div class="description">{ts}Leave this field empty if you do not want to join a team.{/ts}</div>
        {/if}
      </td>
  </tr>
{/if}
</table>
