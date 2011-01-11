=============================================================================
Glossary2 (G2)
-----------------------------------------------------------------------------
 (c) 2005-2011 Frederic G. MARAND
 Licensed under the CeCILL version 2 and General Public License version 2 and later
 $Id$
=============================================================================
0. Table of contents
--------------------

  0. Table of contents
  1. Introduction
  2. Prerequisites
  3. Version warnings
  4. Installing / upgrading / uninstalling
  5. Notice

// Interim HEAD being set up for Drupal 7 port from the latest D6 dev version. //

1. Introduction
---------------

G2 is a glossary management module written for Drupal.

It is not intended as a direct replacement for glossary.module
which has been already available with drupal for quite some time now,
but as an alternative for sites needing a glossary for a large number of entries,
or a different feature set.

Unlike glossary.module, it uses nodes for its definitions, and does not
automatically link terms in other nodes to their entries in G2, but relies on
<dfn> markup in definitions, and can link to terms in multi-byte character sets
and terms containing special characters like slashes or ampersands without
specific markup.

Its development to this date has been entirely sponsored by OSInet.fr

A word of warning: due to G2 targeting high-volume glossaries and including
its own APIs and features, the code is heavier than glossary.module: the base
version source for G2 is more than twice the volume of the base version
source for glossary.module. It may be too heavy for sites on very limited
hosting plans or unable to use clean URLs. On the other hand, it handles
glossaries with thousands of nodes without slowing or increasing its memory
requirements significantly.

Project page on Drupal.org:
        http://drupal.org/project/g2
Documentation wiki (contributions welcome)
        http://wiki.audean.com/g2/start
Sample implementation (about 6000 terms, localized to french)
        http://www.riff.org/glossaire

2. Prerequisites
----------------

  * Drupal 6.x
  * MySQL 5.x, configured for UTF-8 encoding (collating: utf8_general_ci)
  * PHP 5.1

3. VERSION WARNINGS
-------------------

Since 2009-09-27:
- sites not configured with clean URLs are no longer taken into account
- the module is only maintained/evolved for the DRUPAL-4-7 and
  DRUPAL-6--1 branches.
- A 5.0 version exists, but only to ease upgrades from 4.7 to 6.
- A 7.0 version is planned, but not developed yet.

4. Installing / upgrading / uninstalling
----------------------------------------

Installing and upgrading within the DRUPAL-6--1 branch is taken care of thanks
to the standard Drupal install mechanisms.

WARNING: Should you want to uninstall the module, take care to first remove
all G2 nodes before removing the module. This includes:

- your glossary definitions,
- the unpublished page used for the glossary home page skeleton.
- the unpublished page used for the disambiguation skeleton

Unless you do this, you will have inconsistent nodes in your system, because
Drupal will be missing the module to load G2 entries. If you do not modify
any of these nodes, reinstalling the module will restore consistency and
enable a clean noed deletion and uninstall later on.

Upgrading from the 4.7 branch to the 6.1 branch is NOT INCLUDED, so it needs
some manual help. Contact the maintainer if the need arises.

5. Ruby XML-RPC client
----------------------

This version includes a Ruby client demonstrating how to use the G2
XML-RPC services from non-Drupal code. Keep in mind this is basic demo
code, that should probably not be used without extra care in production.

6. NOTICE
---------

6.1 Statistics
--------------
The statistics displayed on the "entries starting by initial ..." page
at URL <drupal>/g2/initial/<some initial segment> mention :

"Displaying 0 entries starting by ... from a total number of ... entries.

It must be understood that this "total number" is actually the total number
a user without administrative permissions can see, that is, published entries.
The "published" epithete is not used because site visitors are not expected
to be aware of the publishing process.

6.2 Random block
----------------

This block only works if the glossary has at least three visible entries.
Since G2 is designed for large glossaries, this is not considered a bug.
