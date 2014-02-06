<?php

require_once 'pcpteams.civix.php';
require_once 'pcpteams.inc.php';

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

  // Add a template to the form region to display the field
  CRM_Core_Region::instance('pcp-form-pcp-fields')->add(array(
    'template' => 'CRM/Pcpteams/ContributionPageSetup.tpl',
  ));
}

/**
 * Form: CRM_PCP_Form_PCPAccount
 * Description: new PCP profile account, store the pcp_team_id in the session.
 * See: pcpteams_civicrm_buildForm()
 */
function pcpteams_civicrm_buildForm_CRM_PCP_Form_PCPAccount(&$form) {
  $session = CRM_Core_Session::singleton();
  $pcp_team_id = CRM_Utils_Request::retrieve('pcp_team_id', 'Positive', $session);
}

/**
 * Form: CRM_PCP_Form_Campaign
 * Description: create/edit a PCP page.
 * See: pcpteams_civicrm_buildForm()
 */
function pcpteams_civicrm_buildForm_CRM_PCP_Form_Campaign(&$form) {
  // Prepare default values (nb: radio buttons are handled differently since setDefault doesn't work)
  $session = CRM_Core_Session::singleton();
  $pcp_team_id = $session->get('pcp_team_id');
  $pcp_id = CRM_Utils_Array::value('pcp_id', $form->_defaultValues);

  $defaults = array();
  $pcp_team_info = NULL;

  if ($pcp_id) {
    // Existing PCP page, so show previously saved values
    $pcp_team_info = pcpteams_getteaminfo($pcp_id);
    $defaults['pcp_team_id'] = $pcp_team_info->civicrm_pcp_id_parent;
    $defaults['pcp_team_type'] = $pcp_team_info->type_id;
    $defaults['pcp_team_notifications'] = $pcp_team_info->notify_on_contrib;
  }
  elseif ($pcp_team_id) {
    // pcp_id in session means that the URL the user received is an invite to a team
    $defaults['pcp_team_id'] = $pcp_team_id;
    $defaults['pcp_team_type'] = CIVICRM_PCPTEAM_TYPE_INDIVIDUAL;
  }
  else {
    $defaults['pcp_team_type'] = CIVICRM_PCPTEAM_TYPE_INDIVIDUAL;
  }

  // For new pages, we keep a hidden field with the first/last name
  // because team members cannot choose a name for their page.
  // (this was a design choice, to keep the team member listings simple).
  if (! $pcp_id) {
    $form->addElement('hidden', 'pcp_team_default_title', $session->get('pcp_team_first_name') . ' ' . $session->get('pcp_team_last_name'));
  }

  // Type of page (new team or individual)
  // for existing pages, we do not allow to change this
  if ($pcp_team_info) {
    $form->addElement('hidden', 'pcp_team_type', $defaults['pcp_team_type'], array('id' => 'pcp_team_type'));

    $e = $form->addElement('text', 'pcp_team_type_description', ts('Type'));
    $e->freeze();
    $defaults['pcp_team_type_description'] = ($defaults['pcp_team_type'] == CIVICRM_PCPTEAM_TYPE_INDIVIDUAL ? ts('Individual') : ts('Team'));
  }
  else {
    $radios = array();
  
    $elements = array(
      CIVICRM_PCPTEAM_TYPE_INDIVIDUAL => array(
        'label' => ts('Individual'),
      ),
      CIVICRM_PCPTEAM_TYPE_TEAM => array(
        'label' => ts('Team'),
      ),
    );
  
    foreach ($elements as $key => $e) {
      if ($defaults['pcp_team_type'] == $key) {
        $options['checked'] = NULL;
      }
  
      $radios[$key] = $form->addElement('radio', NULL, $key, $e['label'], $key, $options);
    }
  
    $form->addGroup($radios, 'pcp_team_type', ts('Type'));
  }

  // If individual, which team to join (may be empty)
  if (! $pcp_team_info || ($pcp_team_info->type_id == CIVICRM_PCPTEAM_TYPE_INDIVIDUAL && $defaults['pcp_team_id'])) {
    $teams = array('' => ts('- select -')) + pcpteams_getteamnames();
  
    // Do not allow to select their own page as a team
    if ($pcp_id && isset($teams[$pcp_id])) {
      unset($teams[$pcp_id]);
    }
  
    $e = $form->addElement('select', 'pcp_team_id', ts('Team'), $teams);

    // we do not allow people to change teams (keep it simple)
    if ($pcp_team_info) {
      $e->freeze();
    }
  }

  // Checkbox to receive contribution notifications
  $form->addElement('checkbox', 'pcp_team_notifications', ts('Notifications'), ts('Notify me by e-mail when a new contribution is received.'));

  $form->setDefaults($defaults);

  // Add a template to the form region to display the field
  CRM_Core_Region::instance('pcp-form-campaign')->add(array(
    'template' => 'CRM/Pcpteams/CampaignPageSetup.tpl',
    'weight' => -1,
  ));

  // Add a template to the form region for the e-mail notification option
  CRM_Core_Region::instance('pcp-form-campaign')->add(array(
    'template' => 'CRM/Pcpteams/CampaignPageSetup-notifications.tpl',
    'weight' => 99,
  ));

  $resources = CRM_Core_Resources::singleton();
  $resources->addStyleFile('ca.bidon.pcpteams', 'pcpteams.css');
  $resources->addScriptFile('ca.bidon.pcpteams', 'pcpteams.js');
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

    case 'CRM_PCP_Form_PCPAccount':
      $session = CRM_Core_Session::singleton();
      $session->set('pcp_team_last_name', CRM_Utils_Array::value('last_name', $form->_submitValues));
      $session->set('pcp_team_first_name', CRM_Utils_Array::value('first_name', $form->_submitValues));
      break;

    case 'CRM_PCP_Form_Campaign':
      $pcp_id = CRM_Utils_Array::value('pcp_id', $form->_defaultValues);
      $pcp_team_id = CRM_Utils_Array::value('pcp_team_id', $form->_submitValues);
      $pcp_team_type = CRM_Utils_Array::value('pcp_team_type', $form->_submitValues);
      $pcp_team_notifications = CRM_Utils_Array::value('pcp_team_notifications', $form->_submitValues);

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

      // E-mail notifications on contribution received
      CRM_Core_DAO::executeQuery("UPDATE civicrm_pcp_team SET notify_on_contrib = " . intval($pcp_team_notifications) . " WHERE civicrm_pcp_id = " . $pcp_id);

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

      $pcp = $smarty->_tpl_vars['pcp'];
      $pcp_team_info = pcpteams_getteaminfo($pcp['pcp_id']);

      $smarty->assign('pcpteams_type_id', $pcp_team_info->type_id);

      if ($pcp_team_info->civicrm_pcp_id_parent) {
        $smarty->assign('pcp_id_parent', $pcp_team_info->civicrm_pcp_id_parent);
  
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

          $achieved = $total / $smarty->_tpl_vars['pcp']['goal_amount'] * 100;

          $smarty->assign('total', $total);
          $smarty->assign('achieved', $achieved);

          CRM_Core_Region::instance('pcp-page-pcpinfo')->add(array(
            'template' => 'CRM/Pcpteams/PCPInfo-team-members.tpl',
            'weight' => 99,
          ));
        }
      }
  
      break;
  }
}

/**
 * Implements hook_civicrm_post().
 */
function pcpteams_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Contribution' && $op == 'create' && $objectRef->soft_credit_to) {
    //get the default domain email address.
    list($domainEmailName, $domainEmailAddress) = CRM_Core_BAO_Domain::getNameAndEmail();

    $pcpcreator = new CRM_Contact_DAO_Contact();
    $pcpcreator->id = $objectRef->soft_credit_to;
    $pcpcreator->find(TRUE);

    $pcpcreatoremail = new CRM_Core_DAO_Email();
    $pcpcreatoremail->contact_id = $objectRef->soft_credit_to;
    $pcpcreatoremail->find(TRUE);

    $contributor = new CRM_Contact_DAO_Contact();
    $contributor->id = $objectRef->contact_id;
    $contributor->find(TRUE);

    $contributoremail = new CRM_Core_DAO_Email();
    $contributoremail->contact_id = $objectRef->contact_id;
    $contributoremail->find(TRUE);

    // NB: because we can't have the exact PCP page, we use the contribution page source
    // Ex: Online Contribution: Name of PCP Page.

    $tplParams = array(
      'pcpName' => $objectRef->source,
      'displayName' => $pcpcreator->display_name,
      'contributorFirstName' => $contributor->first_name,
      'contributorLastName' => $contributor->last_name,
      'contributorEmail' => $contributoremail->email,
      'contributionAmount' => $objectRef->total_amount,
      'currency' => $objectRef->currency,
    );

    $sendTemplateParams = array(
      'groupName' => 'msg_tpl_workflow_contribution',
      'valueName' => 'pcpteams_notification_contribution',
      'contactId' => $pcpcreator->id,
      'toEmail' => $pcpcreatoremail->email,
      'from' => "$domainEmailName <$domainEmailAddress>",
      'tplParams' => $tplParams,
      'isTest' => $objectRef->is_test,
    );

    CRM_Core_BAO_MessageTemplates::sendTemplate($sendTemplateParams);
  }
}

