--
-- Creates the table associating a `civicrm_pcp` page to a parent team page.
--

CREATE TABLE `civicrm_pcp_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Personal Campaign Team ID',
  `civicrm_pcp_id` int(10) unsigned NOT NULL COMMENT 'FK to Personal Campaign Page ID',
  `civicrm_pcp_id_parent` int(10) unsigned NOT NULL COMMENT 'FK to parent Personal Campaign Page ID',
  `status_id` int(10) unsigned NOT NULL COMMENT 'Whether the team relation is active, pending, rejected.',
  `type_id` int(10) unsigned NOT NULL COMMENT 'Individual=1 or team=2 page.',
  PRIMARY KEY (`id`),
  KEY `FK_civicrm_pcp_id` (`civicrm_pcp_id`),
  CONSTRAINT `FK_civicrm_pcp_id` FOREIGN KEY (`civicrm_pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_civicrm_pcp_id_parent` FOREIGN KEY (`civicrm_pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


