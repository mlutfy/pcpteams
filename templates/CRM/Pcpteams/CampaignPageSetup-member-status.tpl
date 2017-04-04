{if $pcp_team_info.team_type == 2 && empty($pcp_team_info.team_id)}
  <h2>Manage Team Members</h2>
  <div class="crm-section crm-pcp-pcp_team_status-section">
  {foreach from=$form key=form_element_name item=form_element}
    {if substr($form_element_name, 0, 22) === 'pcp_team_member_status'}
    <div class="label">{$form.$form_element_name.label}</div>
    <div class="content">{$form.$form_element_name.html}</div>
    <div class="clear"></div>
    {/if}
  {/foreach}
  </div>
{/if}
