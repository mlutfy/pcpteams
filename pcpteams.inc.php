<?php

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

  if (! $pcp_team_id) {
    $pcp_team_id = 'NULL';
  }

  $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_pcp_team WHERE status_id = 1 AND civicrm_pcp_id = " . $pcp_id);

  if ($dao->fetch()) {
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_pcp_team
      SET status_id = 1, civicrm_pcp_id_parent = " . $pcp_team_id . "
      WHERE civicrm_pcp_id = " . $pcp_id
    );
  }
  else {
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_pcp_team (civicrm_pcp_id, civicrm_pcp_id_parent, status_id)
           VALUES ($pcp_id, $pcp_team_id, 1)
    ");
  }
}

/**
 * Get the team of a PCP page.
 */
function pcpteams_getteam($pcp_id) {
  $dao = CRM_Core_DAO::executeQuery("SELECT civicrm_pcp_id_parent FROM civicrm_pcp_team WHERE status_id = 1 AND civicrm_pcp_id = " . $pcp_id);

  if ($dao->fetch()) {
    return $dao->civicrm_pcp_id_parent;
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
function pcpteams_getteamname($id) {
  $teams = array();

  $pcp = new CRM_PCP_DAO_PCP();
  $pcp->id = $id;

  if ($pcp->find(TRUE)) {
    return $pcp->title;
  }

  return '';
}

