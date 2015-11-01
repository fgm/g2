<?php

/**
 * @file
 * Contains G2 Entries controller.
 */

namespace Drupal\g2\Controller;

/**
 * Class Entries contains the controller for the entry list pages.
 *
 * Pages are:
 * - Entries by full name.
 * - Entries by initial.
 */
class Entries {
  /**
   * Title of the G2 pages listing entries.
   */
  const ENTRIES_BY_NAME = 'G2 entries by name';

  /**
   * Title of the G2 by-initial pages
   */
  const ENTRIES_BY_INITIAL = 'entries starting with initial %initial';
}
