<?php

/**
 * Collection of upgrade steps
 */
class CRM_Pcpteams_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Run an external SQL script when the module is installed
   */
  public function install() {
    // onInstall handles the .sql files
  }

  /**
   * Run an external SQL script when the module is uninstalled
   */
  public function uninstall() {
    // onUninstall handles the .sql files
  }

  /**
   * Example: Run a simple query when a module is enabled
   */
  public function enable() {
  }

  /**
   * Example: Run a simple query when a module is disabled
   */
  public function disable() {
  }

  /**
   * New columns for the PCP Team default values
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_5001() {
    $this->ctx->log->info('Applying update 5001');

    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_pcp_block_team ADD `max_members` int(10) DEFAULT 0 COMMENT 'Max members per team'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_pcp_block_team ADD `default_team_goal_amount` decimal(20,2) DEFAULT NULL COMMENT 'Default team goal amount'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_pcp_block_team ADD `default_individual_goal_amount` decimal(20,2) DEFAULT NULL COMMENT 'Default individual goal amount'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_pcp_block_team ADD `default_intro_text` text DEFAULT NULL");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_pcp_block_team ADD `default_page_text` text DEFAULT NULL");
    return TRUE;
  }

}
