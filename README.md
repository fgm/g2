# Glossary 2 (G2)

(c) 2005-2023 Frederic G. MARAND

Licensed under the CeCILL version 2.1 and the General Public License,
version 2 or later.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FGM/g2/badges/quality-score.png?b=8.x-1.x)](https://scrutinizer-ci.com/g/FGM/g2/?branch=8.x-1.x)

**WARNING** 2023-07-02 : This version of the project is only alpha level
at the moment, rapidly evolving with a stable version expected for 2023-07.


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

G2 is a glossary management module written for Drupal 10 and 9.

It is not intended as a direct replacement for legacy [glossary] module,
nor the more recent [glossify] and [lexicon] modules,
but as an alternative for sites needing a glossary for many entries,
or a different feature set.

Unlike [glossary] or [lexicon], it uses nodes instead of terms to hold
definitions, relies on `<dfn>` markup in definitions, and can link to terms
in multi-byte character sets and terms containing special characters like
slashes or ampersands without any specific markup.

For a better user experience, `<dfn>` elements may optionally be inserted
automatically by enabling the _Automatic_ input filter, which will automatically
insert `<dfn>` elements during filtering for entries it recognizes and which are
not in the filter stop list for the active text format.

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

## Completion status as of 2023-07-03 (dev)

### Main features

| Feature           | Block | Service | API | Filter |
|-------------------|:-----:|:-------:|:---:|:------:|
| Alphabar          |   X   |    X    |  X  |        |
| Latest            |   X   |    X    |  X  |        |
| Random            |   X   |    X    |  X  |        |
| Top               |   X   |    X    |  X  |        |
| WOTD              |   X   |    X    |  X  |        |
| Definition filter |       |         |     |   X    |
| Automatic filter  |       |         |     |   X    |


### Pages

| Page          | Status  |
|---------------|---------|
| main          | Done    |
| homonyms      | Done    |
| initial       | Working |
| node add form | Done    |
| settings      | Working |
| WOTD feed     | Done    |

## Prerequisites

* Any Drupal version since 9.0
* A compatible database, configured for UTF-8 encoding (utf8mb4_general_ci)
* PHP 8.1.x or 8.2.x


## Installing / upgrading / uninstalling

### Installing

Installing is Drupal standard: just copy the module, enable it and configure it
by going through each of the tabs starting at `/admin/content/g2/services`

### Upgrading

| From...To |  4.7.y   |   5.y    |   6.y    |    7.y     | 8.y &rarr; 10.y |
|----------:|:--------:|:--------:|:--------:|:----------:|:---------------:|
|     4.7.x | standard | standard |          |            |                 |
|       5.x |          | standard | standard | conf. only |                 |
|       6.x |          |          | standard | conf. only |                 |
|       7.x |          |          |          |  standard  |      n.a.       |
|   8.x-9.x |          |          |          |            |    standard     |

As this matrix shows, beyond Drupal 6.x, upgrade features are limited.

- The D7 upgrade path only convers configuration,
  and there is no standard upgrade path towards D8+:
  these cases always have to be upgraded manually.
- The D9/D10 upgrade is transparent for the module.

## Feature changes in the D9/D10 version

- New in alpha2:
  - the Automatic filter will automatically tag entries in content,
    except those added to its stop list.
  - the TopN block is available again
  - UX improvements in configuration
  - No-code: the WOTD feed is now a view.
- New in alpha1:
  - UX: the Alphabar service configuration now provides a button to
    automatically rebuild the bar from existing G2 entries.
    The bar can then be manually adjusted before saving.
  - The Definition filter is available again.
  - Alphabar, LatestN, Random and WOTD services and block available again.
  - No-code: all custom logic and templates around fields replaced by predefined
    configuration: fields, view displays, and views.


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

### Uninstalling

WARNING: Should you want to uninstall the module, take care to first remove
all _G2 Entry_ nodes before removing the module. This includes:

- your glossary definitions,
- the unpublished page used for the glossary home page skeleton, if any
- the unpublished page used for the disambiguation skeleton, if any

Unless you do this, you will have inconsistent nodes in your system, because
Drupal will be missing the module to load G2 entries. If you do not modify
any of these nodes, reinstalling the module will restore consistency and
enable a clean node deletion and uninstall later on.

Then remove the Automatic and Definition filters from all text formats.

### Statistics

The statistics displayed on the "entries starting by initial ..." page
at URL <drupal>/g2/initial/<some initial segment> mention :

_Displaying...0 entries starting by ... from a total number of ... entries._

It must be understood that this "total number" is actually the total number
a user without administrative permissions can see, that is, published entries.
The "published" epithete is not used because site visitors are not expected
to be aware of the publishing process.
