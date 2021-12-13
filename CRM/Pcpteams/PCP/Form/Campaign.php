<?php

use CRM_Pcpteams_ExtensionUtil as E;

class CRM_Pcpteams_PCP_Form_Campaign {

  /**
   * Step 2 of the PCP creation process
   *
   * @see pcpteams_civicrm_buildForm()
   */
  public static function buildForm(&$form) {
    // Prepare default values (nb: radio buttons are handled differently since setDefault doesn't work)
    $session = CRM_Core_Session::singleton();
    $pcp_team_id = $session->get('pcp_team_id');
    $pcp_id = CRM_Utils_Array::value('pcp_id', $form->_defaultValues);

    $defaults = [];
    $pcp_team_info_template = [];
    $pcp_team_info = NULL;

    // Variables later exposed to Smarty
    $pcp_team_members = [];
    $has_team_members = FALSE;

    if ($pcp_id) {
      // Existing PCP page, so show previously saved values
      $pcp_team_info = pcpteams_getteaminfo($pcp_id);
      $defaults['pcp_team_id'] = $pcp_team_info->civicrm_pcp_id_parent;
      $defaults['pcp_team_type'] = $pcp_team_info->type_id;
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
      $contact_id = $session->get('userID');
      $contact = civicrm_api3('Contact', 'get', [
        'id' => $contact_id,
        'sequential' => 1,
      ]);

      if (!empty($contact['values'][0]['display_name'])) {
        CRM_Core_Resources::singleton()->addVars('pcpteams', [
          'default_title' => $contact['values'][0]['display_name'],
        ]);
      }
    }

    // Type of page (new team or individual)
    // We do not allow to change this for existing pages (or people following a "join this team" link).
    if (! empty($defaults['pcp_team_id'])) {
      $form->addElement('hidden', 'pcp_team_type', $defaults['pcp_team_type'], ['id' => 'pcp_team_type']);
    }
    else if (empty($defaults['pcp_team_id']) && $defaults['pcp_team_type'] == CIVICRM_PCPTEAM_TYPE_TEAM) {
      // Team lead can also not change the team type once it is set.
      $form->addElement('hidden', 'pcp_team_type', $defaults['pcp_team_type'], ['id' => 'pcp_team_type']);
    }
    else {
      $radios = [];

      $elements = [
        CIVICRM_PCPTEAM_TYPE_INDIVIDUAL => [
          'label' => E::ts('This page represents an individual'),
        ],
        CIVICRM_PCPTEAM_TYPE_TEAM => [
          'label' => E::ts('This page represents a team'),
        ],
      ];

      $options = [];
      foreach ($elements as $key => $e) {
        if ($defaults['pcp_team_type'] == $key) {
          $options['checked'] = TRUE;
        }

        $radios[$key] = $form->addElement('radio', NULL, $key, $e['label'], $key, $options);
      }

      $form->addGroup($radios, 'pcp_team_type', E::ts('Type'));
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

      $teams = ['' => ts('- select -')] + pcpteams_getteamnames($component_page_type, $component_page_id);

      // Do not allow to select their own page as a team
      if ($pcp_id && isset($teams[$pcp_id])) {
        unset($teams[$pcp_id]);
      }

      // Check if the team is full
      $full_teams = [];
      $block = pcpteams_pcpblockteam_getvalue($form->_component, $component_page_id);
      $max_members = CRM_Core_DAO::singleValueQuery('SELECT max_members FROM civicrm_pcp_block_team WHERE civicrm_pcp_block_id = %1', [
        1 => [$block['id'], 'Positive'],
      ]);

      if ($max_members) {
        foreach ($teams as $id => $label) {
          if (!$id) {
            // Skip the "- select -" option
            continue;
          }
          $count_members = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_pcp_team WHERE civicrm_pcp_id_parent = %1', [
            1 => [$id, 'Positive'],
          ]);
          if ($count_members >= $max_members) {
            $teams[$id] = E::ts('%1 - full', [1 => $label]);
            $full_teams[] = $id;
          }
        }
      }

      $form->addElement('select', 'pcp_team_id', E::ts('Team'), $teams);

      // Disable the options for full teams
      $e = $form->getElement('pcp_team_id');

      foreach ($e->_options as $key => $val) {
        if (in_array($val['attr']['value'], $full_teams)) {
          $e->_options[$key]['attr']['disabled'] = 'disabled';
        }
      }
    }

    // This is a team page, and no parent
    // Add form elements to manage the status of team members
    if ($pcp_team_info->type_id == CIVICRM_PCPTEAM_TYPE_TEAM && empty($pcp_team_info->civicrm_pcp_id_parent)) {
      $members = pcpteams_getmembers($pcp_id, TRUE);
      foreach ($members as $member_pcp_id => $member) {
        $member_status_radios = [];

        $member_status_elements = [
          CIVICRM_PCPTEAM_STATUS_APPROVED => [
            'label' => E::ts('Approved'),
          ],
          CIVICRM_PCPTEAM_STATUS_DENIED => [
            'label' => E::ts('Denied'),
          ],
        ];

        $has_team_members = TRUE;
        $pcp_team_members[$member_pcp_id] = $member['team_status_id'];
        $defaults["pcp_team_member_status_$member_pcp_id"] = $member['team_status_id'];
        $member_status_options = [];

        foreach ($member_status_elements as $key => $e) {
          if ($defaults["pcp_team_member_status_$member_pcp_id"] == $key) {
            $member_status_options['checked'] = TRUE;
          }

          $member_status_radios[$key] = $form->addElement('radio', NULL, $key, $e['label'], $key, $member_status_options);
        }

        // @todo Suspicious use of ts()
        $form->addGroup($member_status_radios, "pcp_team_member_status_$member_pcp_id", ts('%1', [$member['title']]));
      }

      $smarty->assign('pcp_team_has_team_members', $has_team_members);
      $smarty->assign('pcp_team_members', $pcp_team_members);
    }

    // Default goal amount, intro, page text
    if (!$pcp_id) {
      $pcp_block_team = pcpteams_pcpblockteam_getvalues($form->_component, $form->_pageId);
      $defaults['pcp_intro_text'] = $pcp_block_team['default_intro_text'];
      $defaults['page_text'] = $pcp_block_team['default_page_text'];
      $defaults['goal_amount'] = $pcp_block_team['default_individual_goal_amount'];

      Civi::resources()->addVars('pcpteams', [
        'default_individual_goal_amount' => $pcp_block_team['default_individual_goal_amount'],
        'default_team_goal_amount' => $pcp_block_team['default_team_goal_amount'],
      ]);
    }

    $form->setDefaults($defaults);

    // Add a template to the form region to display the field
    CRM_Core_Region::instance('pcp-form-campaign')->add([
      'template' => 'CRM/Pcpteams/CampaignPageSetup.tpl',
      'weight' => -1,
    ]);

    // Add a template to the form region for the e-mail notification option
    CRM_Core_Region::instance('pcp-form-campaign')->add([
      'template' => 'CRM/Pcpteams/CampaignPageSetup-member-status.tpl',
      'weight' => 100,
    ]);

    Civi::resources()
      ->addStyleFile('ca.bidon.pcpteams', 'pcpteams.css')
      ->addScriptFile('ca.bidon.pcpteams', 'pcpteams.js');
  }

}
