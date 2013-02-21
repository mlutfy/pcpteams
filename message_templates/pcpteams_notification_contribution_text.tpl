{ts 1=$displayName}Dear %1{/ts},

{ts}You have received a new contribution on your Personal Campaign Page:{/ts}

* {ts}First Name:{/ts} {$contributorFirstName}
* {ts}Last Name:{/ts} {$contributorLastName}
* {ts}E-mail:{/ts} {$contributorEmail}
* {ts}Amount:{/ts} {$contributionAmount|crmMoney}

{capture assign=loginUrl}{crmURL p='user' a=true fe=1 h=1}{/capture}
{ts}This is an automatic message. You may disable these notifications by changing your page settings:{/ts}
$loginUrl
