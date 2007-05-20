=============================================================================
Glossary2 (G2)
-----------------------------------------------------------------------------
$Id$
=============================================================================

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

Buzzword-compliance: XML-RPC Web services, AJAX UI (term lookups)

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
Sample implementation (over 5000 terms, localized to french)
        http://www.riff.org/glossaire

==== PREREQUISITES ====

  * Drupal 4.7.6
  * MySQL 4.1.x, configured for UTF-8 encoding (collating: utf8_general_ci)
    MySQL 4.0.x might work in some configurations but is not supported.
  * PHP version sufficient for Drupal. PHP 5.x recommended: a multi-byte problem
    inherent to PHP4.3 has been described and is not fixed by the PHP Group.
    (Drupal issue 109935, PHP issue 25670).

==== VERSION WARNING ====

As of 2007-05-20:
- sites not configured with clean URLs are no longer taken into account
- the module is only maintained/evolved for the DRUPAL-4-7 branch.
- A 5.0 version is not expected to be considered for several months, unless
  someone writes the port or sponsors it (shouldn't cost much).
- It is actually likely that the module will skip straight from 4.7 to 6.
- the module now follows the "new release system"

==== NOTICE ====

The statistics displayed on the "entries starting by initial ..." page
at URL <drupal>/g2/initial/<some initial segment> mention :

"Displaying 0 entries starting by ... from a total number of ... entries.

It must be understood that this "total number" is actually the total number
a user without administrative permissions can see, that is, published entries.
The "published" epithete is not used because site visitors are not expected
to be aware of the publishing process.
