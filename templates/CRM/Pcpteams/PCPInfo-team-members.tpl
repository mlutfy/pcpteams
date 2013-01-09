<div class="civicrm-pcpteams-members">
<div class="civicrm-pcpteams-members-title">{ts}Members{/ts}</div>
<ul>
{foreach from=$pcp_members key=x item=member}
  <li>
    <a class="civicrm-pcpteams-members-title" href="{crmURL p="civicrm/pcp/info" q="reset=1&component=contribute&id=`$x`"}">{$member.title}</a>
    <a class="civicrm-pcpteams-members-amount" href="{crmURL p="civicrm/pcp/info" q="reset=1&component=contribute&id=`$x`"}">{$member.amount|crmMoney}</a>
  </li>
{foreachelse}
  <li class="civicrm-pcpteams-members-none">{ts}No members{/ts}</li>
{/foreach}
</ul>
</div>
