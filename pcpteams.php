<?php

require_once 'pcpteams.civix.php';
require_once 'pcpteams.inc.php';

use CRM_Pcpteams_ExtensionUtil as E;

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
  if ($formName == 'CRM_PCP_Form_Contribute') {
    CRM_Pcpteams_PCP_Form_Contribute::buildForm($form);
  }
  elseif ($formName == 'CRM_PCP_Form_PCPAccount') {
    // @todo Cleanup into separate PHP file
    pcpteams_civicrm_buildForm_CRM_PCP_Form_PCPAccount($form);
  }
  elseif ($formName == 'CRM_PCP_Form_Campaign') {
    CRM_Pcpteams_PCP_Form_Campaign::buildForm($form);
  }
}

/**
 * Form: CRM_PCP_Form_PCPAccount
 * Description: new PCP profile account, store the pcp_team_id in the session.
 * See: pcpteams_civicrm_buildForm()
 */
function pcpteams_civicrm_buildForm_CRM_PCP_Form_PCPAccount(&$form) {
  // Avoid strange bug where this may be called on form submit, and wipe the session data.
  if (! empty($_GET['action']) && $_GET['action'] == 'add') {
    $pcp_team_id = CRM_Utils_Request::retrieve('pcp_team_id', 'Positive');

    $session = CRM_Core_Session::singleton();
    $session->set('pcp_team_id', $pcp_team_id);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function pcpteams_civicrm_postProcess($formName, &$form) {
  switch($formName) {
    case 'CRM_PCP_Form_Contribute':
      CRM_Pcpteams_PCP_Form_Contribute::postProcess($form);
      break;

    case 'CRM_PCP_Form_PCPAccount':
      $session = CRM_Core_Session::singleton();
      $session->set('pcp_team_last_name', CRM_Utils_Array::value('last_name', $form->_submitValues));
      $session->set('pcp_team_first_name', CRM_Utils_Array::value('first_name', $form->_submitValues));
      break;

    case 'CRM_PCP_Form_Campaign':
      $pcp_id = CRM_Utils_Array::value('pcp_id', $form->_defaultValues);
      $pcp_team_id = CRM_Utils_Array::value('pcp_team_id', $form->_submitValues);
      $pcp_team_type = CRM_Utils_Array::value('pcp_team_type', $form->_submitValues);

      // FIXME: If we are creating a new PCP page, how do we get the page ID?
      // Code below is making the dangerous assumptions that new PCP pages are not often created at the same time.
      if (! $pcp_id) {
        $dao = CRM_Core_DAO::executeQuery("SELECT max(id) as id FROM civicrm_pcp");
        if ($dao->fetch()) {
          $pcp_id = $dao->id;
        }
      }

      // This only supports the initial creation for now
      pcpteams_setteam($pcp_id, $pcp_team_id, $pcp_team_type);

      // Update member status.
      // First check that this is a team page, but no parent.
      if ($pcp_team_type == CIVICRM_PCPTEAM_TYPE_TEAM && empty($pcp_team_id)) {
        $members = pcpteams_getmembers($pcp_id, TRUE);
        foreach($members as $member_pcp_id => $member) {
          $pcp_team_member_status = CRM_Utils_Array::value("pcp_team_member_status_{$member_pcp_id}", $form->_submitValues);
          CRM_Core_DAO::executeQuery("UPDATE civicrm_pcp_team SET status_id = %1 WHERE civicrm_pcp_id = %2", [
            1 => [$pcp_team_member_status, 'Integer'],
            2 => [$member_pcp_id, 'Positive'],
          ]);
        }
      }

      // unset the value from the session so that it does not cause problems later on
      // if the team is modified.
      $session = CRM_Core_Session::singleton();
      $session->get('pcp_team_id', NULL);
      break;
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function pcpteams_civicrm_pageRun(&$page) {
  $name = get_class($page);

  switch($name) {
    case 'CRM_PCP_Page_PCPInfo':
      // Fetch the team pcp_id, if any, to display the team name
      $smarty = CRM_Core_Smarty::singleton();

      $pcp = $smarty->get_template_vars()['pcp'];
      $pcp_team_info = pcpteams_getteaminfo($pcp['pcp_id']);

      if (!$pcp_team_info) {
        return;
      }

      $smarty->assign('pcpteams_type_id', $pcp_team_info->type_id);

      if ($pcp_team_info->civicrm_pcp_id_parent) {
        $smarty->assign('pcp_id_parent', $pcp_team_info->civicrm_pcp_id_parent);
        $smarty->assign('pcp_team_status_id', $pcp_team_info->status_id);

        CRM_Core_Region::instance('pcp-page-pcpinfo')->add(array(
          'template' => 'CRM/Pcpteams/PCPInfo-team-name.tpl',
          'weight' => -1,
        ));
      }
      else {
        // not a team member, so check if we are a team and have members
        // TODO: show non-approved members to group managers?
        if ($pcp_team_info->type_id == CIVICRM_PCPTEAM_TYPE_TEAM) {
          $members = pcpteams_getmembers($pcp['pcp_id']);
          $smarty->assign('pcp_members', $members);

          // Calculate the total received for each members + to the team directly.
          $total = CRM_PCP_BAO_PCP::thermoMeter($pcp['pcp_id']);
          $total += pcpteams_getamountraised($pcp['pcp_id']);

          $achieved = $total / $smarty->get_template_vars()['pcp']['goal_amount'] * 100;

          $honor = $page->get_template_vars('honor');
          foreach ($members as $dao => $member) {
            if (!empty($member['honor'])) {
              $honor = array_merge($honor, $member['honor']);
            }
          }

          $smarty->assign('total', $total);
          $smarty->assign('achieved', $achieved);
          $page->assign('honor', $honor);

          CRM_Core_Region::instance('pcp-page-pcpinfo')->add(array(
            'template' => 'CRM/Pcpteams/PCPInfo-team-members.tpl',
            'weight' => 99,
          ));
        }
      }
      $resources = CRM_Core_Resources::singleton();
      $resources->addStyleFile('ca.bidon.pcpteams', 'pcpteams.css');

      break;
  }
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function pcpteams_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _pcpteams_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
