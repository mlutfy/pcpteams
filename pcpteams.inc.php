<?php

define('CIVICRM_PCPTEAM_TYPE_INDIVIDUAL', 1);
define('CIVICRM_PCPTEAM_TYPE_TEAM', 2);

define('CIVICRM_PCPTEAM_STATUS_APPROVED', 1);
define('CIVICRM_PCPTEAM_STATUS_DENIED', 2);


/**
 * Helper functions.
 */

/**
 * Returns the current pcpblock "team" is_active value.
 * e.g. whether the form allows PCP by teams.
 */
function pcpteams_pcpblockteam_getvalue($target_entity_type, $target_entity_id) {
  $pcpblock = new CRM_PCP_DAO_PCPBlock();
  $pcpblock->target_entity_type = $target_entity_type;
  $pcpblock->target_entity_id = $target_entity_id;

  if ($pcpblock->find(TRUE)) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_block_team WHERE civicrm_pcp_block_id = %1", [
      1 => [$pcpblock->id, 'Positive'],
    ]);

    if ($dao->fetch()) {
      return $dao->is_active;
    }
  }

  return FALSE;
}

/**
 * Returns all the information about the pcp_block_team
 *
 * @todo Convert this to a proper BAO.
 */
function pcpteams_pcpblockteam_getvalues($target_entity_type, $target_entity_id) {
  $pcpblock = new CRM_PCP_DAO_PCPBlock();
  $pcpblock->target_entity_type = $target_entity_type;
  $pcpblock->target_entity_id = $target_entity_id;

  if ($pcpblock->find(TRUE)) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_block_team WHERE civicrm_pcp_block_id = %1", [
      1 => [$pcpblock->id, 'Positive'],
    ]);

    if ($dao->fetch()) {
      return $dao->toArray();
    }
  }

  return FALSE;
}

/**
 * Sets the current pcpblock "team" is_active value.
 * e.g. whether the form allows PCP by teams.
 *
 * @todo Replace with a BAO function create when we have proper entities
 */
function pcpteams_pcpblockteam_setvalue($params) {
  $pcpblock = new CRM_PCP_DAO_PCPBlock();
  $pcpblock->target_entity_type = $params['target_entity_type'];
  $pcpblock->target_entity_id = $params['target_entity_id'];

  // @todo Replace by Api4 or proper BAO calls
  if ($pcpblock->find(TRUE)) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_block_team WHERE civicrm_pcp_block_id = %1", [
      1 => [$pcpblock->id, 'Positive'],
    ]);

    if ($dao->fetch()) {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_pcp_block_team
        SET is_active = %1, max_members = %2, default_team_goal_amount = %3, default_individual_goal_amount = %4, default_intro_text = %5, default_page_text = %6
        WHERE civicrm_pcp_block_id = %7", [
        1 => [$params['pcp_team_is_active'] ?: 0, 'Integer'],
        2 => [$params['max_members'] ?: 0, 'Integer'],
        3 => [$params['default_team_goal_amount'] ?: 0, 'Float'],
        4 => [$params['default_individual_goal_amount'] ?: 0, 'Float'],
        5 => [$params['default_intro_text'] ?? '', 'String'],
        6 => [$params['default_page_text'] ?? '', 'String'],
        7 => [$dao->civicrm_pcp_block_id, 'Positive'],
      ]);
    }
    else {
      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_pcp_block_team (is_active, max_members, civicrm_pcp_block_id, default_individual_goal_amount, default_intro_text, default_page_text, civicrm_pcp_block_id)
        VALUES (%1, %2, %3, %4, %5, %6, %7)", [
        1 => [$params['pcp_team_is_active'] ?: 0, 'Integer'],
        2 => [$params['max_members'] ?: 0, 'Integer'],
        3 => [$params['default_team_goal_amount'] ?: 0, 'Float'],
        4 => [$params['default_individual_goal_amount'] ?: 0, 'Float'],
        5 => [$params['default_intro_text'] ?? '', 'String'],
        6 => [$params['default_page_text'] ?? '', 'String'],
        7 => [$pcpblock->id, 'Positive'],
      ]);
    }
  }
  else {
    throw new Exception('Could not find the PCPBlock for entity ' . $target_entity_type . '/' . $target_entity_id);
  }
}

/**
 * Sets the team for a PCP page.
 * If the team is NULL, assumes it is a new team.
 *
 * @param Int $pcp_id ID of the PCP page in civicrm_pcp
 * @param Int $pcp_team_id ID of the PCP team (NULL means the page is not part of a team).
 * @param Int $pcp_type_id Type of PCP page (CIVICRM_PCPTEAM_TYPE_TEAM or CIVICRM_PCPTEAM_TYPE_INDIVIDUAL).
 * @returns void.
 */
function pcpteams_setteam($pcp_id, $pcp_team_id, $pcp_type_id) {
  // If it is a team page, make sure we do not allow to be part of another team
  if ($pcp_type_id == CIVICRM_PCPTEAM_TYPE_TEAM) {
    $pcp_team_id = NULL;
  }

  // Strict validation of the type of PCP id, since we don't want bad data in the DB.
  $valid_team_types = array(
    CIVICRM_PCPTEAM_TYPE_TEAM,
    CIVICRM_PCPTEAM_TYPE_INDIVIDUAL,
  );

  if (! in_array($pcp_type_id, $valid_team_types)) {
    CRM_Core_Error::fatal('Invalid PCP type received.');
  }

  // Check if the PCP page already has a record associating it (or not) to a team.
  $params = array(
    1 => array($pcp_id, 'Positive'),
  );

  $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_team WHERE civicrm_pcp_id = %1", $params);

  if ($dao->fetch()) {
/*
  [ML] do not allow to update for now (change type or team).. too many things to manage.
  [PeaceWorks] going to assume it's ok to to set a parent, if no parent exists yet (below)
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_pcp_team
      SET status_id = 1,
        civicrm_pcp_id_parent = $pcp_team_id
        type_id = $pcp_type_id
      WHERE civicrm_pcp_id = " . $pcp_id
    );
*/
  }
  else {
    if ($pcp_team_id) {
      // Check if there's an existing record, that just doesn't have a parent yet
      $dao2 = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_team WHERE civicrm_pcp_id = %1", $params);
      if ($dao2->fetch()) {
        // A PCP record exists, but no parent
        $sql = "UPDATE civicrm_pcp_team SET civicrm_pcp_id_parent=%1 WHERE civicrm_pcp_id=%2";
   
        // Assuming we can ignore other params like pcp_type_id here...?
        // We'll only update the team id, and leave the other fields to be managed elsewhere
        $params = array(
          1 => array($pcp_team_id, 'Integer'),
          2 => array($pcp_id, 'Positive'),
        );
      }
      else {
        // No record in civicrm_pcp_team for this PCP
        $sql = "INSERT INTO civicrm_pcp_team (civicrm_pcp_id, civicrm_pcp_id_parent, status_id, type_id)
                VALUES (%1, %2, 1, %3)";
   
        $params = array(
          1 => array($pcp_id, 'Positive'),
          2 => array($pcp_team_id, 'Integer'),
          3 => array($pcp_type_id, 'Integer'),
        );
      }
      CRM_Core_DAO::executeQuery($sql, $params);
    }
    else {
      $sql = "INSERT INTO civicrm_pcp_team (civicrm_pcp_id, civicrm_pcp_id_parent, status_id, type_id)
              VALUES (%1, NULL, 1, %3)";

      $params = array(
        1 => array($pcp_id, 'Positive'),
        3 => array($pcp_type_id, 'Integer'),
      );

      CRM_Core_DAO::executeQuery($sql, $params);
    }
  }
}

/**
 * Get the civicrm_pcp_team record.
 */
function pcpteams_getteaminfo($pcp_id) {
  $pcp_id = intval($pcp_id);
  $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_team WHERE civicrm_pcp_id = " . $pcp_id);

  if ($dao->fetch()) {
    return $dao;
  }

  return NULL;
}

/**
 * Returns a list of PCP-Teams.
 *
 * @param String $component_page_type
 *     Contribute/event (although right now only contribute is fully supported).
 * @param Int    $component_page_id
 *     ID of the contribution page for which we want to list teams.
 * @returns Array
 *     Returns a sorted list of teams, keyed on the PCP ID.
 */
function pcpteams_getteamnames($component_page_type = 'contribute', $component_page_id = NULL) {
  $teams = array();

  // Since 4.6, editing a contribution PCP page seems to not have the $form->_component.
  // c.f. PR #3.
  if (! $component_page_type) {
    $component_page_type = 'contribute';
  }

  $sql = "
    SELECT pcp.id, pcp.title
      FROM civicrm_pcp_team t
      LEFT JOIN civicrm_pcp pcp ON (t.civicrm_pcp_id = pcp.id)
     WHERE civicrm_pcp_id_parent IS NULL
       AND type_id = %1
       AND pcp.is_active = 1
       AND pcp.page_type = %2";

  $params = array(
    1 => array(CIVICRM_PCPTEAM_TYPE_TEAM, 'Positive'),
    2 => array($component_page_type, 'String'),
  );

  if ($component_page_id) {
    $sql .= ' AND pcp.page_id = %3';
    $params[3] = array($component_page_id, 'Positive');
  }

  $dao = CRM_Core_DAO::executeQuery($sql, $params);

  while ($dao->fetch()) {
    $teams[$dao->id] = $dao->title;
  }

  natcasesort($teams);
  return $teams;
}

/**
 * Returns the name of a PCP team by ID
 */
function pcpteams_getteamname($pcp_id) {
  $teams = array();

  $pcp_id = intval($pcp_id);
  $pcp_team_info = pcpteams_getteaminfo($pcp_id);

  if ($pcp_team_info->civicrm_pcp_id_parent) {
    $pcp = new CRM_PCP_DAO_PCP();
    $pcp->id = $pcp_team_info->civicrm_pcp_id_parent;

    if ($pcp->find(TRUE)) {
      return $pcp->title;
    }
  }

  return '';
}

/**
 * Returns the PCP team members, if any.
 * We keep the result cached because it can be called multiple times in a page.
 * Ex: for the member listing, and "total amount raised".
 */
function pcpteams_getmembers($pcp_id, $show_non_approved = FALSE) {
  static $members = [];

  $pcp_id = intval($pcp_id);

  if (isset($members[$pcp_id])) {
    return $members[$pcp_id];
  }

  // Get the status_id for 'approved'
  $approved = CRM_Core_PseudoConstant::getKey('CRM_PCP_BAO_PCP', 'status_id', 'Approved');

  // Get the members of the team
  $members[$pcp_id] = [];

  $dao = CRM_Core_DAO::executeQuery("
    SELECT team.civicrm_pcp_id as id, member.title, member.is_active, team.status_id as team_status_id
      FROM civicrm_pcp_team team
     INNER JOIN civicrm_pcp member ON (member.id = team.civicrm_pcp_id)
     WHERE civicrm_pcp_id_parent = %1 "
    . ($show_non_approved ? '' : " AND team.status_id = 1 ")
    . ' AND member.status_id = ' . $approved
    . ' ORDER BY member.title asc', [
    1 => [$pcp_id, 'Positive'],
  ]);

  // Initialize empty members for this pcp
  $members[$pcp_id] = [];

  while ($dao->fetch()) {
    $members[$pcp_id][$dao->id] = [
      'title' => $dao->title,
      'amount' => CRM_PCP_BAO_PCP::thermoMeter($dao->id),
      'is_active' => $dao->is_active,
      'honor' => CRM_PCP_BAO_PCP::honorRoll($dao->id),
      'team_status_id' => $dao->team_status_id,
    ];
  }

  return $members[$pcp_id];
}

/**
 * Calculates the amount raised for a team.
 * For individuals we can used directly CRM_PCP_BAO_PCP::thermoMeter($pcp_id)
 */
function pcpteams_getamountraised($pcp_id) {
  $total = 0;
  $members = pcpteams_getmembers($pcp_id);

  foreach ($members as $key => $val) {
    $total += $val['amount'];
  }

  return $total;
}

