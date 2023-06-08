# Glossary2 (G2)

(c) 2005-2023 Frederic G. MARAND

Licensed under the CeCILL version 2.1 and the General Public License,
version 2 or later.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FGM/g2/badges/quality-score.png?b=8.x-1.x)](https://scrutinizer-ci.com/g/FGM/g2/?branch=8.x-1.x)

**WARNING** 2023-05-28 : This version of the project is only partially usable
at the moment : it is very much a work in progress.
An complete usable version is expected before 2023-07-01.

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

* Project page on Drupal.org:
  https://drupal.org/project/g2
* Documentation wiki (contributions welcome)
  https://wiki.audean.com/g2/start
* Sample implementation (about 6000 terms, localized to french)
  https://riff.org/glossaire

## Completion status as of 2023-06-09

### Main features

| Feature  | Block   | Service | API     | Block / controller |
|----------|---------|---------|---------|--------------------|
| Alphabar | Working | Working | Working | Working            |
| API      | n.a.    | Working | n.a.    |                    |
| Latest   | Working | Working | Working | Working            |
| Random   | Done    | Done    |         |                    |
| Top      | Working | Working | Working | Working            |
| WOTD     | Done    | Done    |         |                    |

* 'Done' status means working and with test coverage deemed sufficient.
* 'Working' status means appears to work but not (completely) tested.

### Pages

| Page          | Status  |
|---------------|---------|
| main          | Working |
| entries       | Working |
| initial       | Working |
| node add form | Working |
| settings      | Working |
| WOTD feed     | TBD     |


## Prerequisites

* Any Drupal version since 9.0
* A compatible database, configured for UTF-8 encoding (utf8mb4_general_ci)
* PHP 8.1.x or 8.2.x


## Version notes

Since 2009-09-27:

- sites not configured with clean URLs are no longer taken into account
- the module is only maintained/evolved for the Drupal 9.x/10.x branches.


## Installing / upgrading / uninstalling
### Upgrading

|   From...To |  4.7.y   |   5.y    |   6.y    |    7.y     | 8.y &rarr; 10.y |
|------------:|:--------:|:--------:|:--------:|:----------:|:---------------:|
|       4.7.x | standard | standard |          |            |                 |
|         5.x |          | standard | standard | conf. only |                 |
|         6.x |          |          | standard | conf. only |                 |
|         7.x |          |          |          |  standard  |      n.a.       |
| 8.x to 10.x |          |          |          |            |    standard     |

As this matrix shows, beyond Drupal 6.x, update features are limited.
The D7 upgrade path only convers configuration,
and no standard upgrade path exists towards D8+:
these case always have to be upgraded manually.


## Feature changes in the D9/D10 version

- G2 versions since Drupal 4.x have been manually handling the display
  configuration on G2 entries.
  Since fields and view modes have been a standard for quite a long time and
  provide a better administrative UI, all the display configuration and specific
  templates have been removed in favor of using specific view modes.

## Ruby XML-RPC client

This version includes a Ruby client demonstrating how to use the G2
XML-RPC services from non-Drupal code. Keep in mind this is basic demo
code, that should not be used without extra care in production.

Use like:

```bash
ruby g2_client.rb http://my.remote.glossary.server/xmlrpc.php
```

## NOTICE

### Minimum content

The random service / block / API, as well as the automatic WOTD change,
which relies on that service, only work if the glossary has at least three
visible entries.

Since G2 is designed for large glossaries, this is not considered a bug.

---

Everything below this line related to the 7.x-1.x branch, and is likely to be
incorrect for Drupal 8+.

---

### Installing

Installing is Drupal standard: just copy the module, enable it and configure it.

### Uninstalling

WARNING: Should you want to uninstall the module, take care to first remove
all _G2 Entry_ nodes before removing the module. This includes:

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
