{crmScope key="ca.bidon.pcpteams"}
{if $pcp_team_info.team_type == 2 && empty($pcp_team_info.team_id)}
  <h2>{ts}Manage Team Members{/ts}</h2>
  <div class="crm-section crm-pcp-pcp_team_status-section">
    {if $pcp_team_has_team_members}
      {foreach from=$pcp_team_members key=member_pcp_id item=member_status}
        {capture assign="form_element_name"}pcp_team_member_status_{$member_pcp_id}{/capture}
        <div class="label">{$form.$form_element_name.label}</div>
        <div class="content">{$form.$form_element_name.html}</div>
        <div class="clear"></div>
      {/foreach}
    {else}
      <p>{ts}The team currently does not have any members.{/ts}</p>
    {/if}
  </div>
{/if}
{/crmScope}
