// Hide the team title if the person is joining a team
// NB: the link the person received may include a pre-set team ID
function pcpteams_profile_toggle_title() {
  if (cj('#pcp_team_id').val()) {
    // Has a team, so hide the title, the title will be his/her name
    cj('.crm-contribution-form-block-title').hide();
    if (cj('input[name="pcp_team_default_title"]').length > 0) {
      cj('#pcp_title').val(cj('input[name="pcp_team_default_title"]').val());
    }
  }
  else {
    // Team page, or individual without a team
    cj('.crm-contribution-form-block-title').slideDown('slow');
  }
}

cj(document).ready(function() {
  pcpteams_profile_toggle_title();

  cj('#pcp_team_id').change(function() {
    pcpteams_profile_toggle_title();
  });
});
