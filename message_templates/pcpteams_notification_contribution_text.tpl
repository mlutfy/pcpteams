{ts 1=$contact.display_name}Dear %1{/ts},

{ts 1=$pcpName}You have received a new contribution on your Personal Campaign Page: %1.{/ts}

* {ts}First Name:{/ts} {$contributorFirstName}
* {ts}Last Name:{/ts} {$contributorLastName}
* {ts}E-mail:{/ts} {$contributorEmail}
* {ts}Amount:{/ts} {$contributionAmount|crmMoney}

{capture assign=pcpEditUrl}{crmURL p='civicrm/pcp/info' q="reset=1&action=update&id=`$pcpId`&component=contribute" a=true fe=1 h=1}{/capture}
{ts}This is an automatic message. You may disable these notifications by changing your page settings:{/ts}
$pcpEditUrl
