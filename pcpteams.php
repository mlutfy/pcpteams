<?php

require_once 'pcpteams.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function pcpteams_civicrm_config(&$config) {
  _pcpteams_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function pcpteams_civicrm_xmlMenu(&$files) {
  _pcpteams_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function pcpteams_civicrm_install() {
  return _pcpteams_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function pcpteams_civicrm_uninstall() {
  return _pcpteams_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function pcpteams_civicrm_enable() {
  return _pcpteams_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function pcpteams_civicrm_disable() {
  return _pcpteams_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function pcpteams_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _pcpteams_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function pcpteams_civicrm_managed(&$entities) {
  return _pcpteams_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_buildForm().
 */
function pcpteams_civicrm_buildForm($formName, &$form) {
  $f = 'pcpteams_civicrm_buildForm_' . $formName;

  if (function_exists($f)) {
    $f($form);
  }
}

/**
 * Form: CRM_PCP_Form_Contribute
 * Description: PCP configuration for a contribution page. Allow to enable PCP teams per form.
 * See: pcpteams_civicrm_buildForm()
 */
function pcpteams_civicrm_buildForm_CRM_PCP_Form_Contribute(&$form) {
  $form->addElement('checkbox', 'pcp_team_active', ts('Enable Personal Campaign Pages for Teams?'));

  $target_entity_table = CRM_Utils_Array::value('target_entity_table', $form->_defaultValues);
  $target_entity_id    = CRM_Utils_Array::value('target_entity_id', $form->_defaultValues);
  $pcp_team_active     = pcpteams_pcpblockteam_getvalue($target_entity_table, $target_entity_id);

  $defaults = array(
    'pcp_team_active' => $pcp_team_active,
  );

  $form->setDefaults($defaults);

  CRM_Core_Region::instance('pcp-fields')->add(array(
    'template' => 'CRM/Pcpteams/ContributionPageSetup.tpl',
  ));
}

/**
 * Implements hook_civicrm_postProcess().
 */
function pcpteams_civicrm_postProcess($formName, &$form) {
  switch($formName) {
    case 'CRM_PCP_Form_Contribute':
      $target_entity_type = CRM_Utils_Array::value('target_entity_type', $form->_defaultValues);
      $target_entity_id   = CRM_Utils_Array::value('target_entity_id', $form->_defaultValues);
      $pcp_team_active    = CRM_Utils_Array::value('pcp_team_active', $form->_submitValues);

      pcpteams_pcpblockteam_setvalue($target_entity_type, $target_entity_id, $pcp_team_active);

      break;
  }
}

/**
 * Returns the current pcpblock "team" is_active value.
 * e.g. whether the form allows PCP by teams.
 */
function pcpteams_pcpblockteam_getvalue($target_entity_type, $target_entity_id) {
  $pcpblock = new CRM_PCP_DAO_PCPBlock();
  $pcpblock->target_entity_type = $target_entity_type;
  $pcpblock->target_entity_id = $target_entity_id;

  if ($pcpblock->find(TRUE)) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_block_team WHERE civicrm_pcp_block_id = " . $pcpblock->id);

    if ($dao->fetch()) {
      return $dao->is_active;
    }
  }

  return FALSE;
}

/**
 * Sets the current pcpblock "team" is_active value.
 * e.g. whether the form allows PCP by teams.
 */
function pcpteams_pcpblockteam_setvalue($target_entity_type, $target_entity_id, $pcp_team_active) {
  $pcpblock = new CRM_PCP_DAO_PCPBlock();
  $pcpblock->target_entity_type = $target_entity_type;
  $pcpblock->target_entity_id = $target_entity_id;

  $pcp_team_active = intval($pcp_team_active);

  if ($pcpblock->find(TRUE)) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_block_team WHERE civicrm_pcp_block_id = " . $pcpblock->id);

    if ($dao->fetch()) {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_pcp_block_team SET is_active = " . $pcp_team_active . " WHERE civicrm_pcp_block_id = " . $dao->civicrm_pcp_block_id);
    }
    else {
      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_pcp_block_team (civicrm_pcp_block_id, is_active)
                                  VALUES ({$pcpblock->id}, $pcp_team_active)");
    }
  }
  else {
    CRM_Core_Error::fatal(ts('Could not find the PCPBlock for entity: %1 %2', array(1 => $target_entity_id, 2 => $target_entity_type)));
  }
}

