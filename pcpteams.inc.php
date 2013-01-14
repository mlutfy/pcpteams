<?php

define('CIVICRM_PCP_TEAM_TYPE_INDIVIDUAL', 1);
define('CIVICRM_PCP_TEAM_TYPE_TEAM', 2);


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

/**
 * Sets the team for a PCP page.
 * If the team is NULL, assumes it is a new team.
 */
function pcpteams_setteam($pcp_id, $pcp_team_id) {
  $pcp_team_id = intval($pcp_team_id);
  $pcp_type_id = ($pcp_type_id ? 2 : 1); // 2 = team

  if (! $pcp_team_id) {
    $pcp_team_id = 'NULL';
  }

  $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_team WHERE status_id = 1 AND civicrm_pcp_id = " . $pcp_id);

  if ($dao->fetch()) {
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_pcp_team
      SET status_id = 1,
        civicrm_pcp_id_parent = $pcp_team_id
        type_id = $pcp_type_id
      WHERE civicrm_pcp_id = " . $pcp_id
    );
  }
  else {
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_pcp_team (civicrm_pcp_id, civicrm_pcp_id_parent, status_id, type_id)
           VALUES ($pcp_id, $pcp_team_id, 1, $pcp_type_id)
    ");
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
 */
function pcpteams_getteamnames() {
  $teams = array();

  $dao = CRM_Core_DAO::executeQuery("
    SELECT pcp.id, pcp.title
      FROM civicrm_pcp_team t
      LEFT JOIN civicrm_pcp pcp ON (t.civicrm_pcp_id = pcp.id)
     WHERE civicrm_pcp_id_parent IS NULL
  ");

  while ($dao->fetch()) {
    $teams[$dao->id] = $dao->title;
  }

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
 * Returns the PCP team members, if any
 */
function pcpteams_getmembers($pcp_id, $show_non_approved = FALSE) {
  $members = array();

  $pcp_id = intval($pcp_id);

  $dao = CRM_Core_DAO::executeQuery("
    SELECT team.civicrm_pcp_id as id, member.title
      FROM civicrm_pcp_team team
     INNER JOIN civicrm_pcp member ON (member.id = team.civicrm_pcp_id)
     WHERE civicrm_pcp_id_parent = " . $pcp_id
    . ($show_non_approved ? '' : " AND team.status_id = 1 ")
    . ' ORDER BY member.title asc '
  );

  while ($dao->fetch()) {
    $members[$dao->id] = array(
      'title' => $dao->title,
      'amount' => pcpteams_getamountraised($dao->id),
    );
  }

  return $members;
}

function pcpteams_getamountraised($pcp_id) {
  return 0; // TODO
}

