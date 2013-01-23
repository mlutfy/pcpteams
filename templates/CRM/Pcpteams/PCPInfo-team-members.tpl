<table class="civicrm-pcpteams-members">
<tbody>
  <tr>
    <th class="civicrm-pcpteams-members-title">{$pcp.title}</th>
    <th class="civicrm-pcpteams-members-amount">{ts}Raised{/ts}</th>
  </tr>
{foreach from=$pcp_members key=x item=member}
  <tr>
    <td class="civicrm-pcpteams-members-title"><a href="{crmURL p="civicrm/pcp/info" q="reset=1&component=contribute&id=`$x`"}">{$member.title}</a></td>
    <td class="civicrm-pcpteams-members-amount">{$member.amount|crmMoney}</td>
  </li>
{foreachelse}
  <tr>
    <td colspan="2" class="civicrm-pcpteams-members-none">{ts}No members{/ts}</td>
  </tr>
{/foreach}
</table>
