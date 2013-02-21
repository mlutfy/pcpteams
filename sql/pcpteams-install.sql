--
-- Creates the table associating a civicrm_pcp page to a parent team page.
-- Note: when a parent PCP page is deleted, the child becomes an individual without a team.
--

CREATE TABLE `civicrm_pcp_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Personal Campaign Team ID',
  `civicrm_pcp_id` int(10) unsigned NOT NULL COMMENT 'FK to Personal Campaign Page ID',
  `civicrm_pcp_id_parent` int(10) unsigned DEFAULT NULL COMMENT 'FK to parent Personal Campaign Page ID',
  `status_id` int(10) unsigned NOT NULL COMMENT 'Whether the team relation is active, pending, rejected.',
  `type_id` int(10) unsigned NOT NULL COMMENT 'Individual=1 or team=2 page.',
  `notify_on_contrib` tinyint(4) unsigned NOT NULL COMMENT 'Send an e-mail to PCP page owner on new donation.',
  PRIMARY KEY (`id`),
  KEY `FK_civicrm_pcp_id` (`civicrm_pcp_id`),
  KEY `FK_civicrm_pcp_id_parent` (`civicrm_pcp_id_parent`),
  CONSTRAINT `FK_civicrm_pcp_id` FOREIGN KEY (`civicrm_pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_civicrm_pcp_id_parent` FOREIGN KEY (`civicrm_pcp_id_parent`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Settings for the PCP Block, e.g. whether teams are allowed or not
--

CREATE TABLE `civicrm_pcp_block_team` (
  `civicrm_pcp_block_id` int(10) unsigned NOT NULL COMMENT 'FK to PCP block Id',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is Personal Campaign Team functionality enabled/active?',
  KEY `FK_civicrm_pcp_block_id` (`civicrm_pcp_block_id`),
  CONSTRAINT `FK_civicrm_pcp_block_id` FOREIGN KEY (`civicrm_pcp_block_id`) REFERENCES `civicrm_pcp_block` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

