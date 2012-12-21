{if $pcp_id_parent}
    <div id="civicrm-pcpteams-pcpinfo-teamname"><span class="label">{ts}Team:{/ts}</span> <a href="{crmURL p="civicrm/pcp/info" q="reset=1&id=`$pcp_id_parent`&component=contribute"}">{$pcp.pcp_id|pcpteams_getteamname}</a></div>
{/if}
