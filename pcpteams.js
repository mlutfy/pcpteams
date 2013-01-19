// Hide the team title if the person is joining a team
// NB: the link the person received may include a pre-set team ID
function pcpteams_profile_toggle_title() {
  if (cj('#pcp_team_id').val()) {
    cj('.crm-contribution-form-block-title').hide();
    cj('#pcp_title').val(cj('input[name="pcp_team_default_title"]').val());
  }
  else {
    cj('.crm-contribution-form-block-title').slideDown('slow');
    cj('#pcp_title').val('');
  }
}

cj(document).ready(function() {
  pcpteams_profile_toggle_title();

  cj('#pcp_team_id').change(function() {
    pcpteams_profile_toggle_title();
  });
});
