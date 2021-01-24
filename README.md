# Glossary2 (G2)

(c) 2005-2021 Frederic G. MARAND

Licensed under the CeCILL version 2.1 and General Public License version 2 and later.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FGM/g2/badges/quality-score.png?b=8.x-1.x)](https://scrutinizer-ci.com/g/FGM/g2/?branch=8.x-1.x)

**WARNING** 2015-11-28 : This version of the project is _not_ usable at the
  moment : it is very much a work in progress. An initial usable version is
  expected before 2016-01-01.


## Table of contents

- Table of contents
- Introduction
- Completion status
- Prerequisites
- Version warnings
- Installing / upgrading / uninstalling
- Ruby XML-RPC client
- Notice


## Introduction

G2 is a glossary management module written for Drupal.

It is not intended as a direct replacement for [glossary], which has been
available with drupal for a number of years, or the more recent [glossify] and
[lexicon] modules, but as an alternative for sites needing a glossary for many
entries, or a different feature set.

Unlike [glossary] or [lexicon], it uses nodes instead of terms to hold
definitions, and does not automatically link terms in other nodes to their
entries in G2, but relies on <dfn> markup in definitions, and can link to terms
in multi-byte character sets and terms containing special characters like
slashes or ampersands without any specific markup.

[glossary]: https://www.drupal.org/project/glossary
[glossify]: https://www.drupal.org/project/glossify
[lexicon]: https://www.drupal.org/project/lexicon

It has been designed to handle glossaries with many thousands of nodes without
slowing or increasing its memory requirements significantly.

Its development to this date has been entirely sponsored by [OSInet].

[OSInet]: https://osinet.fr

* Project page on Drupal.org: https://drupal.org/project/g2
* Documentation wiki (contributions welcome) https://wiki.audean.com/g2/start
* Sample implementation (about 6000 terms, localized to french) https://riff.org/glossaire


## Completion status
### Main features

| Blocks    | Service     | API       | Block / controller  |
|-----------|-------------|-----------|---------------------|
| Alphabar  | Done        | Working   | Working             |
| API       | n.a.        | Working   | n.a.                |
| Latest    | Working     | Working   | Working             |
| Random    |             |           |                     |
| Top       | Working     | Working   | Working             |
| WOTD      |             |           |                     |

* 'Done' status means working and with high test coverage
* 'Working' status means appears to work but not (completely) tested

### Pages

| Page          | Status
|---------------|-------------------------------------------|
| main          | Working                                   |
| entries       | Working                                   |
| initial       | Working                                   |
| node add form | Working                                   |
| settings      | Working                                   |
| WOTD feed     |                                           |

At this point in the module port, the composer.json file is only here to allow
Scrutinizer to find core classes, not for actual deployment.


## Prerequisites

* Any Drupal versions since 8.0
* A compatible database, configured for UTF-8 encoding (collating: utf8mb4_general_ci)
* PHP 7.4.x

## Version notes

Since 2009-09-27:

- sites not configured with clean URLs are no longer taken into account
- the module is only maintained/evolved for the Drupal 6.x and 9.x branches.

## Installing / upgrading / uninstalling

### Upgrading

| From...To | 4.7.y    | 5.y      | 6.y        | 7.y        | 8.y, 9.y |
|----------:|:--------:|:--------:|:----------:|:----------:|:--------:|
| 4.7.x     | standard | standard |            |            |          |
| 5.x       |          | standard | standard   | conf. only |          |
| 6.x       |          |          | standard   | conf. only |          |
| 7.x       |          |          |            | standard   | n.a.     |
| 8.x, 9.x  |          |          |            |            | standard |

As this matrix shows, beyond Drupal 6.x, update features are limited: the D7 upgrade path only convers configuration, and no standard upgrade path exists towards D8/D9: these case always have to be upgraded manually.

## Ruby XML-RPC client

This version includes a Ruby client demonstrating how to use the G2
XML-RPC services from non-Drupal code. Keep in mind this is basic demo
code, that should not be used without extra care in production.

Use like:

```bash
ruby g2_client.rb http://my.remote.glossary.server/xmlrpc.php
```


---

Everything below this line related to the 7.x-1.x branch, and is likely to be
incorrect for 8.x-1.x

---

### Installing

Installing is Drupal standard: just copy the module, enable it and configure it:

- module configuration starts at admin/config/content/g2/block


### Uninstalling

WARNING: Should you want to uninstall the module, take care to first remove
all G2 nodes before removing the module. This includes:

- your glossary definitions,
- the unpublished page used for the glossary home page skeleton.
- the unpublished page used for the disambiguation skeleton

Unless you do this, you will have inconsistent nodes in your system, because
Drupal will be missing the module to load G2 entries. If you do not modify
any of these nodes, reinstalling the module will restore consistency and
enable a clean node deletion and uninstall later on.

## NOTICE
### Statistics

The statistics displayed on the "entries starting by initial ..." page
at URL <drupal>/g2/initial/<some initial segment> mention :

"Displaying 0 entries starting by ... from a total number of ... entries.

It must be understood that this "total number" is actually the total number
a user without administrative permissions can see, that is, published entries.
The "published" epithete is not used because site visitors are not expected
to be aware of the publishing process.

### Random block

This block only works if the glossary has at least three visible entries.
Since G2 is designed for large glossaries, this is not considered a bug.
