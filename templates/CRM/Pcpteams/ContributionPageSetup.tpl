{crmScope key="ca.bidon.pcpteams"}
<table class="form-layout">
  <tr class="crm-contribution-contributionpage-pcp-form-block-pcp_team_is_active">
    <td class="label">&nbsp;</td>
    <td>{$form.pcp_team_is_active.html} {$form.pcp_team_is_active.label}</td>
  </tr>
  <tr class="crm-contribution-contributionpage-pcp-form-block-max_members crm-pcpteams-setting">
    <td class="label">{$form.max_members.label}</td>
    <td>
      {$form.max_members.html}
      <div class="description">{ts}Limit the maximum number of members per team? Set to 0 to disable limits.{/ts}</div>
    </td>
  </tr>
  <tr class="crm-contribution-contributionpage-pcp-form-block-default_team_goal_amount crm-pcpteams-setting">
    <td class="label">{$form.default_team_goal_amount.label}</td>
    <td>
      {$form.default_team_goal_amount.html}
      <div class="description">{ts}Default team goal amount, when creating a new PCP team page. Set to 0 to disable.{/ts}</div>
    </td>
  </tr>
  <tr class="crm-contribution-contributionpage-pcp-form-block-default_individual_goal_amount crm-pcpteams-setting">
    <td class="label">{$form.default_individual_goal_amount.label}</td>
    <td>
      {$form.default_individual_goal_amount.html}
      <div class="description">{ts}Default individual goal amount, when creating a new PCP individual page. Set to 0 to disable.{/ts}</div>
    </td>
  </tr>
  <tr class="crm-contribution-contributionpage-pcp-form-block-default_intro_text crm-pcpteams-setting">
    <td class="label">{$form.default_intro_text.label}</td>
    <td>
      {$form.default_intro_text.html}
      <div class="description">{ts}Default "welcome" text when creating a new PCP page. Users will be able to adapt it, or enter their own message.{/ts}</div>
    </td>
  </tr>
  <tr class="crm-contribution-contributionpage-pcp-form-block-default_page_text crm-pcpteams-setting">
    <td class="label">{$form.default_page_text.label}</td>
    <td>
      {$form.default_page_text.html}
      <div class="description">{ts}Default "Your Message" text when creating a new PCP page. Users will be able to adapt it, or enter their own message.{/ts}</div>
    </td>
  </tr>
</table>
{/crmScope}

{literal}
<script>
  CRM.$(function($) {
    function pcpteams_settings_visibility(value) {
      if (value) {
        $('.crm-pcpteams-setting').show();
      }
      else {
        $('.crm-pcpteams-setting').hide();
      }
    }

    $('#pcp_team_is_active').on('change', function() {
      pcpteams_settings_visibility($(this).is(':checked'));
    });

    // On load
    pcpteams_settings_visibility($('#pcp_team_is_active').is(':checked'));
  });
</script>
{/literal}
