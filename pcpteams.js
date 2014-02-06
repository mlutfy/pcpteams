cj(function($) {
  // Hide the team title if the person is joining a team
  // NB: the link the person received may include a pre-set team ID
  function pcpteams_profile_toggle_title() {
    if ($('#pcp_team_id').val()) {
      // Has a team, so hide the title, the title will be his/her name
      $('.crm-contribution-form-block-title').hide();
      if ($('input[name="pcp_team_default_title"]').length > 0) {
        $('#pcp_title').val($('input[name="pcp_team_default_title"]').val());
      }
    }
    else {
      // Team page, or individual without a team
      $('.crm-contribution-form-block-title').slideDown('slow');
    }
  }

  // Hide the list of teams if the person is signing up to create a new team.
  // We do not allow to have teams within teams.
  function pcpteams_profile_toggle_teamlist() {
    if ($('input[name="pcp_team_type"]:checked').val() == 1) {
      // Individual, so may chose from a team
      $('.crm-contribution-form-block-pcp_team_name').slideDown('slow');
    }
    else {
      // Team, so hide list of teams to join
      $('.crm-contribution-form-block-pcp_team_name').hide();
    }
  }

  pcpteams_profile_toggle_title();
  $('#pcp_team_id').change(function() {
    pcpteams_profile_toggle_title();
  });

  pcpteams_profile_toggle_teamlist();
  $('#crm-container #Campaign input[name="pcp_team_type"]').on('click', function() {
    pcpteams_profile_toggle_teamlist();
  });
});
