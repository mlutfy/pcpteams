DROP TABLE IF EXISTS `civicrm_pcp_team`;
DROP TABLE IF EXISTS `civicrm_pcp_block_team`;

DELETE FROM `civicrm_msg_template`
WHERE `msg_title` = 'Contributions - PCP contribution notification';

SELECT @option_group_id_contribution := max(id)
FROM `civicrm_option_group`
WHERE `name` = 'msg_tpl_workflow_contribution';

DELETE FROM `civicrm_option_value`
WHERE
  `option_group_id` = @option_group_id_contribution AND
  `name` = 'pcpteams_notification_contribution';
