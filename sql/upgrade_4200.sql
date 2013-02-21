--
-- Message templates for e-mail notifications
-- Based on CRM/Upgrade/Incremental/sql/README.txt
--

SELECT @option_group_id_contribution := max(id) from civicrm_option_group where name = 'msg_tpl_workflow_contribution';
SELECT @max_val    := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_contribution;
SELECT @max_wt     := max(weight) from civicrm_option_value where option_group_id=@option_group_id_contribution;

INSERT INTO civicrm_option_value (option_group_id, {localize field='label'}label{/localize}, {localize field='description'}description{/localize}, value, name, weight)
VALUES (@option_group_id_contribution, {localize}'Contributions - Notification to PCP owner'{/localize},{localize}'Sends an e-mail notification to the PCP owner when he/she receives a contribution.'{/localize}, (SELECT @max_val := @max_val+1), 'pcpteams_notification_contribution', (SELECT @max_wt := @max_wt+1));

SELECT @tpl_ovid := MAX(id) FROM civicrm_option_value WHERE option_group_id = @option_group_id_contribution;

{fetch assign=text file="$extDir/message_templates/pcpteams_notification_contribution_text.tpl"}
{fetch assign=html file="$extDir/message_templates/pcpteams_notification_contribution_html.tpl"}

INSERT INTO civicrm_msg_template (msg_title, msg_subject, msg_text, msg_html, workflow_id, is_default, is_reserved)
VALUES ('Contributions - PCP contribution notification', '{literal}{$pcpName}{/literal}: new contribution', '{$text|escape:"quotes"}', '{$html|escape:"quotes"}', @tpl_ovid, 1, 0);

