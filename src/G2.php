<?php

/**
 * @file
 * Contains G2.
 *
 * @copyright 2005-2015 Frédéric G. Marand, for Ouest Systemes Informatiques.
 */

namespace Drupal\g2;

/**
 * Class G2 is the container for general-use G2 data.
 */
class G2 {
  // The name of the node.type.g2_entry config entity.
  const NODE_TYPE = 'g2_entry';

  /**
   * The G2 permission for normal users.
   */
  const PERM_VIEW = 'view g2 entries';

  /**
   * The G2 permission for administrators.
   */
  const PERM_ADMIN = 'administer g2 entries';

  /**
   * The public-facing version: two first levels for semantic versioning.
   */
  const VERSION = '8.1';

  /**
   * Encodes terminal path portions for G2.
   *
   * This allows linking to things containing #, + or '.', like 'C++', 'C#' or
   * the '.' initial.
   *
   * Warning: this is NOT a generic replacement for urlencode, but covers a very
   * specific G2-related need.
   *
   * @param string $terminal
   *   The terminal rune to encode.
   *
   * @return string
   *   The encoded terminal.
   */
  public static function encodeTerminal($terminal) {
    $terminal = strtr($terminal, array(
      '.' => '%2E',
      '/' => '%2F',
      '#' => '%23',
      '&' => '%26',
      '+' => '%2B',
    ));
    return $terminal;
  }

}
