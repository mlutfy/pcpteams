# CiviCRM Personnal Campaign Teams

CiviCRM personal campaign teams allows people to create their own PCP page
which is part of a team, with a common objective as well as an individual
page for each member.

## Warning

This extension is still minimally maintained, but in Spring 2015, Veda
Consulting developed a similar extension with more features:

https://github.com/veda-consulting/uk.co.vedaconsulting.pcpteams

## Features

Enabling the extension will allow the following:

* In contribution pages, when enabling the PCP feature, this extension
  adds a new checkbox to enable the "team" feature as well.

* In the PCP page creation form, adds a "type" field, so that users can
  specify whether they are creating an individual or team page.  If they
  select "individual", another field appears to let them choose a team
  (optional field).

* If they are part of a team, the title of their PCP page will be their
  first+last name. This makes it easier and less confusing, for example,
  to show a list of team members. If they do not join a team, they can
  choose the title of their page.

* Team pages do not have an honor-roll, but instead a listing of team
  members. This is to avoid cluttering too much the UI, but you can still
  re-enable it in the templates.

* The thermometer of team pages includes the contribution amounts of
  the team members.

* PCP page owners can enable notifications to receive an e-mail every
  time they receive a contribution to their page. This is not specific
  to PCP-teams, and would be a nice feature in CiviCRM core.  Note that
  we cannot send them a copy of the transaction receipt, because it may
  be too much information, or may include a tax receipt.

* Contributors can decide whether to give directly to the team, or to
  a specific team member.

## Installation

* Unpack the module code in your CiviCRM extension directory, then enable.

* If you are using CiviCRM 4.2, you will need to patch a few templates. (TODO)
  No need to patch for CiviCRM 4.3 and later.

To download:
https://github.com/mlutfy/civicrmpcpteams

More information:
http://civicrm.org/blogs/mlutfy/personal-campaign-page-teams

## Copyright and license

(C) 2012-2015 Mathieu Lutfy
https://www.symbiotic.coop

Redistributed under the AGPL license:
https://civicrm.org/licensing
