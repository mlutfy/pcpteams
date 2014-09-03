CiviCRM PCP Teams
=================

CiviCRM personal campaign teams allows people to create their own personal
campaign page (PCP) which is part of a team, with a common objective as well
as an individual page for each member.

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

To download the latest version of this extension:
https://github.com/mlutfy/ca.bidon.pcpteams

More information:
https://civicrm.org/blogs/mlutfy/personal-campaign-page-teams

Requirements
============

- CiviCRM >= 4.4

Installation
============

* Unpack the module code in your CiviCRM extension directory, then enable.

* This module requires a patch on CiviCRM core. See PATCHES.txt.
  One patch is to add a SoftCredit "hook_civicrm_post", the other is optional,
  so that "tell a friend" inherits the default subject/body configuration from
  the contribution form.

Support
=======

Please post bug reports in the issue tracker of this project on github:
https://github.com/mlutfy/ca.bidon.pcpteams/issues

For general questions and support, please post on the "Extensions" forum:
http://forum.civicrm.org/index.php/board,57.0.html

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

If you appreciate this module, please consider donating 10$ to the CiviCRM project:
http://civicrm.org/participate/support-civicrm

While I do my best to provide volunteer support for this extension, please
consider financially contributing to support or development of this extension
if you can.

Commercial support via Coop SymbioTIC: <https://www.symbiotic.coop>

Or you can send me the equivalent of a beer: <https://www.bidon.ca/en/paypal>

License
=======

(C) 2012-2014 Mathieu Lutfy <mathieu@bidon.ca>
https://www.bidon.ca/

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details or https://civicrm.org/licensing.
