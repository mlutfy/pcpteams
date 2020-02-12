cj(function($) {
  // Hide the team title if the person is joining a team
  // NB: the link the person received may include a pre-set team ID
  function pcpteams_profile_toggle_title() {
    if ($('input[name="pcp_team_id"]').val() || $('select#pcp_team_id').val()) {
      // Has a team, so hide the title, the title will be his/her name
      $('#Campaign .crm-contribution-form-block-title').hide();
      if (typeof CRM.vars.pcpteams.default_title != 'undefined') {
        $('#pcp_title').val(CRM.vars.pcpteams.default_title);
      }
    }
    else {
      // Team page, or individual without a team
      $('#Campaign .crm-contribution-form-block-title').slideDown('slow');
    }
  }

  // Hide the list of teams if the person is signing up to create a new team.
  // We do not allow to have teams within teams.
  function pcpteams_profile_toggle_teamlist() {
    if ($('input[name="pcp_team_type"]:checked').val() == 1 || $('input[name="pcp_team_type"][type="hidden"]').val() == 1) {
      // Individual, so may chose from a team
      $('#Campaign .crm-pcp-team-name-section').slideDown('slow');
    }
    else {
      // Team, so hide list of teams to join
      $('.crm-pcp-team-name-section').slideUp();
      $('select#pcp_team_id').val('');
      pcpteams_profile_toggle_title();
    }
  }

  pcpteams_profile_toggle_title();
  $('#crm-container #Campaign select#pcp_team_id').change(function() {
    pcpteams_profile_toggle_title();
  });

  pcpteams_profile_toggle_teamlist();
  $('#crm-container #Campaign input[name="pcp_team_type"]').on('click', function() {
    // if the user changes from individual to 'create new team',
    // hide the person's name (pcp_title), since that will probably not be the team name.
    $('#pcp_title').val('');
    pcpteams_profile_toggle_teamlist();
  });
});
