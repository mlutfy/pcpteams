CiviCRM PCP Teams
=================

CiviCRM personal campaign teams allows people to create their own personal
campaign page (PCP) which is part of a team, with a common objective as well
as an individual page for each member.

https://civicrm.org/extensions/pcp-teams

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

* Contributors can decide whether to give directly to the team, or to
  a specific team member.

Installation
============

To download the latest version of this extension:
https://github.com/mlutfy/pcpteams

* Unpack the module code in your CiviCRM extension directory, then enable.

* This module proposes an optional patch on CiviCRM core. See PATCHES.txt.
  It makes "tell a friend" inherit the default subject/body configuration from
  the contribution form.

More information:
https://civicrm.org/blogs/mlutfy/personal-campaign-page-teams

Requirements
============

- CiviCRM >= 5.0

Support
=======

Please post bug reports in the issue tracker of this project on github:  
https://github.com/mlutfy/ca.bidon.pcpteams/issues

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

If you appreciate this extension, please consider supporting the CiviCRM project:  
https://civicrm.org/support-us

While I do my best to provide volunteer support for this extension, please
consider financially contributing to support or development of this extension
if you can.

Commercial support via Coop SymbioTIC: <https://www.symbiotic.coop>

License
=======

(C) 2012-2020 Mathieu Lutfy  
https://www.symbiotic.coop/en

Redistributed under the AGPL license:  
https://civicrm.org/licensing
