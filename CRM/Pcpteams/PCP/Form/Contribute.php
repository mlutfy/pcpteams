<?php

use CRM_Pcpteams_ExtensionUtil as E;

class CRM_Pcpteams_PCP_Form_Contribute {

  /**
   * PCP configuration for a contribution page. Allow to enable PCP teams per form.
   *
   * @see pcpteams_civicrm_buildForm()
   */
  public static function buildForm(&$form) {
    $form->add('checkbox', 'pcp_team_is_active', E::ts('Enable Personal Campaign Pages for Teams?'));
    $form->add('text', 'max_members', E::ts('Max members per teams?'));
    $form->add('text', 'default_team_goal_amount', E::ts('Default team goal'));
    $form->add('text', 'default_individual_goal_amount', E::ts('Default individual goal'));
    $form->add('textarea', 'default_intro_text', E::ts('Default welcome text'), ['class' => 'big']);
    $form->add('wysiwyg', 'default_page_text', E::ts('Default Your Message text'));

    // If it is an existing PCP Block
    if (!empty($form->_defaultValues['target_entity_type'])) {
      $target_entity_type = $form->_defaultValues['target_entity_type'];
      $target_entity_id = $form->_defaultValues['target_entity_id'];
      $defaults = pcpteams_pcpblockteam_getvalues($target_entity_type, $target_entity_id);
      $defaults['pcp_team_is_active'] = $defaults['is_active'] ?? 0;
      $form->setDefaults($defaults);
    }

    // Add a template to the form region to display the field
    CRM_Core_Region::instance('pcp-form-pcp-fields')->add([
      'template' => 'CRM/Pcpteams/ContributionPageSetup.tpl',
    ]);
  }

  /**
   * @see pcpteams_civicrm_postProcess()
   */
  public static function postProcess(&$form) {
    $values = $form->exportValues();
    $values['target_entity_type'] = $form->_defaultValues['target_entity_type'];
    $values['target_entity_id'] = $form->_defaultValues['target_entity_id'];

    // @todo Replace with a BAO create
    pcpteams_pcpblockteam_setvalue($values);
  }

}
