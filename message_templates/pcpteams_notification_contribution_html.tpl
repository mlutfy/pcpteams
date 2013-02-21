<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title></title>
</head>
<body>

{capture assign=headerStyle}colspan="2" style="text-align: left; padding: 4px; border-bottom: 1px solid #999; background-color: #eee;"{/capture}
{capture assign=labelStyle }style="padding: 4px; border-bottom: 1px solid #999; background-color: #f7f7f7;"{/capture}
{capture assign=valueStyle }style="padding: 4px; border-bottom: 1px solid #999;"{/capture}

<center>
 <table width="500" border="0" cellpadding="0" cellspacing="0" id="crm-event_receipt" style="font-family: Arial, Verdana, sans-serif; text-align: left;">

  <!-- BEGIN HEADER -->
  <!-- You can add table row(s) here with logo or other header elements -->
  <!-- END HEADER -->

  <!-- BEGIN CONTENT -->

  <tr>
   <td>
     <p>{ts 1=$contact.display_name}Dear %1{/ts},</p>

     {capture assign=pcpUrl}{crmURL p='civicrm/pcp/info' q="reset=1&id=`$pcpId`" a=true fe=1 h=1}{/capture}
     <p>{ts 1=$pcpUrl 2=$pcpName}You have received a new contribution on your Personal Campaign Page: <a href="%1">%2</a>.{/ts}</p>

     <ul>
       <li>{ts}First Name:{/ts} {$contributorFirstName}</li>
       <li>{ts}Last Name:{/ts} {$contributorLastName}</li>
       <li>{ts}E-mail:{/ts} {$contributorEmail}</li>
       <li>{ts}Amount:{/ts} {$contributionAmount|crmMoney}</li>
     </ul>

     {capture assign=pcpEditUrl}{crmURL p='civicrm/pcp/info' q="reset=1&id=`$contributionPageId`" a=true fe=1 h=1}{/capture}
     <p>{ts 1=$pcpEditUrl}This is an automatic message. You may disable these notifications by <a href="%1">changing your page settings</a>.{/ts}</p>
   </td>
  </tr>

  <!-- END CONTENT -->

 </table>
</center>

</body>
</html>
