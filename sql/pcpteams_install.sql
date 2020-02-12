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
