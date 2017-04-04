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
  // Avoid strange bug where this may be called on form submit, and wipe the session data.
  if (! empty($_GET['action']) && $_GET['action'] == 'add') {
    $pcp_team_id = CRM_Utils_Request::retrieve('pcp_team_id', 'Positive');

    $session = CRM_Core_Session::singleton();
    $session->set('pcp_team_id', $pcp_team_id);
  }
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
  $pcp_team_info_template = array();
  $pcp_team_info = NULL;

  if ($pcp_id) {
    // Existing PCP page, so show previously saved values
    $pcp_team_info = pcpteams_getteaminfo($pcp_id);
    $defaults['pcp_team_id'] = $pcp_team_info->civicrm_pcp_id_parent;
    $defaults['pcp_team_type'] = $pcp_team_info->type_id;
    $defaults['pcp_team_notifications'] = $pcp_team_info->notify_on_contrib;
    $pcp_team_info_template['team_id'] = $pcp_team_info->civicrm_pcp_id_parent;
    $pcp_team_info_template['team_type'] = $pcp_team_info->type_id;
  }
  elseif ($pcp_team_id) {
    // pcp_id in session means that the URL the user received is an invite to a team
    $defaults['pcp_team_id'] = $pcp_team_id;
    $defaults['pcp_team_type'] = CIVICRM_PCPTEAM_TYPE_INDIVIDUAL;
    $pcp_team_info_template['team_id'] = $pcp_team_id;
    $pcp_team_info_template['team_type'] = CIVICRM_PCPTEAM_TYPE_INDIVIDUAL;
  }
  else {
    $defaults['pcp_team_type'] = CIVICRM_PCPTEAM_TYPE_INDIVIDUAL;
    $pcp_team_info_template['team_type'] = CIVICRM_PCPTEAM_TYPE_INDIVIDUAL;
    $pcp_team_info_template['team_id'] = NULL;
  }

  // Add team information to template variables
  // TODO: this doesn't really belong here. relocate to the civi form run hook or somewhere better
  $smarty = CRM_Core_Smarty::singleton();
  $smarty->assign('pcp_team_info', $pcp_team_info_template);

  // For new pages, we keep a hidden field with the first/last name
  // because team members cannot choose a name for their page.
  // (this was a design choice, to keep the team member listings simple).
  if (! $pcp_id) {
    $form->addElement('hidden', 'pcp_team_default_title', $session->get('pcp_team_first_name') . ' ' . $session->get('pcp_team_last_name'));
  }

  // Type of page (new team or individual)
  // We do not allow to change this for existing pages (or people following a "join this team" link).
  if (! empty($defaults['pcp_team_id'])) {
    $form->addElement('hidden', 'pcp_team_type', $defaults['pcp_team_type'], array('id' => 'pcp_team_type'));
  }
  else if (empty($defaults['pcp_team_id']) && $defaults['pcp_team_type'] == CIVICRM_PCPTEAM_TYPE_TEAM) {
    // Team lead can also not change the team type once it is set.
    $form->addElement('hidden', 'pcp_team_type', $defaults['pcp_team_type'], array('id' => 'pcp_team_type'));
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
        $options['checked'] = TRUE;
      }

      $radios[$key] = $form->addElement('radio', NULL, $key, $e['label'], $key, $options);
    }

    $form->addGroup($radios, 'pcp_team_type', ts('Type'));
  }

  // If individual, which team to join (may be empty)
  if (! empty($defaults['pcp_team_id'])) {
    // we do not allow people to change teams (keep it simple)
    $form->addElement('hidden', 'pcp_team_id', $defaults['pcp_team_id']);
  }
  else {
    // Taken from PCP/Form/Campaign.php postProcess
    $component_page_type = $form->_component;
    $component_page_id = $form->get('component_page_id') ? $form->get('component_page_id') : $form->_contriPageId;

    $teams = array('' => ts('- select -')) + pcpteams_getteamnames($component_page_type, $component_page_id);

    // Do not allow to select their own page as a team
    if ($pcp_id && isset($teams[$pcp_id])) {
      unset($teams[$pcp_id]);
    }

    $form->addElement('select', 'pcp_team_id', ts('Team'), $teams);
  }

  // Checkbox to receive contribution notifications
  $form->addElement('checkbox', 'pcp_team_notifications', ts('Notifications'), ts('Notify me by e-mail when a new contribution is received.'));

  // this is a team page, but no parent.
  if ($pcp_team_info->type_id == CIVICRM_PCPTEAM_TYPE_TEAM && empty($pcp_team_info->civicrm_pcp_id_parent)) {
    $members = pcpteams_getmembers($pcp_id, TRUE);
    foreach($members as $dao => $member) {
      $member_status_radios = array();

      $member_status_elements = array(
        CIVICRM_PCPTEAM_STATUS_APPROVED => array(
          'label' => ts('Approved'),
        ),
        CIVICRM_PCPTEAM_STATUS_DENIED => array(
          'label' => ts('Denied'),
        ),
      );

      $defaults["pcp_team_member_status_$dao"] = $member['team_status_id'];
      $member_status_options = array();
      foreach ($member_status_elements as $key => $e) {
        if ($defaults["pcp_team_member_status_$dao"] == $key) {
          $member_status_options['checked'] = TRUE;
        }

        $member_status_radios[$key] = $form->addElement('radio', NULL, $key, $e['label'], $key, $member_status_options);
      }

      $form->addGroup($member_status_radios, "pcp_team_member_status_$dao", ts('%1', array($member['title'])));
    }
  }

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

  // Add a template to the form region for the e-mail notification option
  CRM_Core_Region::instance('pcp-form-campaign')->add(array(
    'template' => 'CRM/Pcpteams/CampaignPageSetup-member-status.tpl',
    'weight' => 100,
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
      pcpteams_setteam($pcp_id, $pcp_team_id, $pcp_team_type, $pcp_team_notifications);

      // E-mail notifications on contribution received
      CRM_Core_DAO::executeQuery("UPDATE civicrm_pcp_team SET notify_on_contrib = " . intval($pcp_team_notifications) . " WHERE civicrm_pcp_id = " . $pcp_id);

      // Update member status.
      // First check that this is a team page, but no parent.
      if ($pcp_team_type == CIVICRM_PCPTEAM_TYPE_TEAM && empty($pcp_team_id)) {
        $members = pcpteams_getmembers($pcp_id, TRUE);
        foreach($members as $member_dao => $member) {
          $pcp_team_member_status = CRM_Utils_Array::value("pcp_team_member_status_${member_dao}", $form->_submitValues);
          $member_params = array(
              1 => array($pcp_team_member_status, 'Integer'),
              2 => array($member_dao, 'Positive'),
          );
          CRM_Core_DAO::executeQuery("UPDATE civicrm_pcp_team SET status_id = %1 WHERE civicrm_pcp_id = %2", $member_params);
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

      break;
  }
}

/**
 * Implements hook_civicrm_post().
 */
function pcpteams_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'SoftCredit' && $op == 'create') {
    //get the default domain email address.
    list($domainEmailName, $domainEmailAddress) = CRM_Core_BAO_Domain::getNameAndEmail();

    // FIXME: objectRef is received as an array, but should be an object.
    // i.e. need to fix the patch on core.

    $contrib = new CRM_Contribute_DAO_Contribution();
    $contrib->id = $objectRef['contribution_id'];
    $contrib->find(TRUE);

    $pcpcreator = civicrm_api3('Contact', 'getsingle', array(
      'id' => $objectRef['contact_id'],
    ));

    $contributor = new CRM_Contact_DAO_Contact();
    $contributor->id = $contrib->contact_id;
    $contributor->find(TRUE);

    $contributoremail = new CRM_Core_DAO_Email();
    $contributoremail->contact_id = $contrib->contact_id;
    $contributoremail->find(TRUE);

    // NB: because we can't have the exact PCP page, we use the contribution page source
    // Ex: Online Contribution: Name of PCP Page.
    // FIXME: since 4.4 we have the specific pcp_id in the soft_credit object.
    $tplParams = array(
      'pcpName' => $contrib->source,
      'displayName' => $pcpcreator['display_name'],
      'contributorFirstName' => $contributor->first_name,
      'contributorLastName' => $contributor->last_name,
      'contributorEmail' => $contributoremail->email,
      'contributionAmount' => $contrib->total_amount,
      'currency' => $contrib->currency,
    );

    $sendTemplateParams = array(
      'groupName' => 'msg_tpl_workflow_contribution',
      'valueName' => 'pcpteams_notification_contribution',
      'contactId' => $pcpcreator['contact_id'],
      'toEmail' => $pcpcreator['email'],
      'from' => "$domainEmailName <$domainEmailAddress>",
      'tplParams' => $tplParams,
      'isTest' => $contrib->is_test,
    );

    CRM_Core_BAO_MessageTemplate::sendTemplate($sendTemplateParams);
  }
}

