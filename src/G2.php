<?php

namespace Drupal\g2;

/**
 * Class G2 is the container for general-use G2 data.
 */
class G2 {
  /**
   * The key for the module configuration.
   */
  const CONFIG_NAME = 'g2.settings';

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
   * The API format.
   */
  const API_VERSION = 8;

  /**
   * Block: alphabar.
   */
  const DELTA_ALPHABAR = 'alphabar';

  /**
   * Block: n most recent.
   */
  const DELTA_LATEST = 'latest';

  /**
   * Block: random.
   */
  const DELTA_RANDOM = 'random';

  /**
   * Block: n most viewed.
   */
  const DELTA_TOP = 'top';

  /**
   * Block: word of the day.
   */
  const DELTA_WOTD = 'wotd';

  // Constants in this group are only listed to remove WSODs, but they still
  // need the associated logic to be converted from variables to config.
  const VARNOFREETAGGING = 'g2-no-freetagging';
  const DEFNOFREETAGGING = FALSE;
  const VARREMOTEG2 = 'g2-remote';
  const DEFREMOTEG2 = FALSE;
  const DEFREMOTENO = '';
  const VARTOOLTIPS = 'g2-tooltips';
  const DEFTOOLTIPS = FALSE;
  const VARPAGETITLE = 'g2-page-title';
  const DEFPAGETITLE = TRUE;
  const VARWOTDENTRY = 'g2-wotd-entry';
  const DEFWOTDENTRY = '';
  const VARRANDOMSTORE = 'g2-random-store';
  const DEFRANDOMSTORE = TRUE;
  const VARRANDOMENTRY = 'g2-random-entry';
  const DEFRANDOMENTRY = '';
  const VARRANDOMTERMS = 'g2-random-terms';
  const DEFRANDOMTERMS = [];
  const VARWOTDTERMS = 'g2-wotd-terms';
  const DEFWOTDTERMS = FALSE;
  // Default is DEFWOTDTITLE.
  const VARWOTDFEEDTITLE = 'g2-wotd-feed-title';
  const VARWOTDTITLE = 'g2-wotd-title';
  const DEFWOTDTITLE = '';
  const VARWOTDFEEDDESCR = 'g2-wotd-feed-description';
  const DEFWOTDFEEDDESCR = '';
  const VARWOTDBODYSIZE = 'g2-wotd-body-size';
  const DEFWOTDBODYSIZE = 100;
  const VARWOTDAUTOCHANGE = 'g2-wotd-auto-change';
  const DEFWOTDAUTOCHANGE = TRUE;
  const VARWOTDFEEDLINK = 'g2-wotd-feed-link';
  const DEFWOTDFEEDLINK = TRUE;
  const VARWOTDFEEDAUTHOR = 'g2-wotd-feed-author';
  const DEFWOTDFEEDAUTHOR = '';
  // Uses mktime() for default.
  const VARWOTDDATE = 'g2-wotd-date';
  const VARPATHMAIN = 'g2-path-main';
  const DEFPATHMAIN = 'g2.main';
  const VARHIDDENTITLE = 'g2-hidden-title';
  const DEFHIDDENTITLE = FALSE;
  const VARLOGREFERRERS = 'g2-log-referrers';
  const DEFLOGREFERRERS = TRUE;
  const VARTOPITEMCOUNT = 'g2-top-item-count';
  const DEFTOPITEMCOUNT = 5;

  // Constants in this group are only listed to remove WSODs, but they still
  // need to be converting from hook_menu to Symfony routing.
  const PATH_AUTOCOMPLETE = 'g2/autocomplete';
  const PATH_ENTRIES = 'g2/entries';
  const PATH_INITIAL = 'g2/initial';
  const PATH_NODE_ADD = 'node/add/g2';
  const PATH_SETTINGS = 'g2/admin/settings';
  const PATH_WOTD_FEED = 'g2/wotd/feed';


  const TITLE_MAIN = 'Glossary';
  const TITLE_WOTD_FEED = 'Word of the day';

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
    $terminal = strtr($terminal, [
      '.' => '%2E',
      '/' => '%2F',
      '#' => '%23',
      '&' => '%26',
      '+' => '%2B',
    ]);
    return $terminal;
  }

  /**
   * Return the API version.
   *
   * @return int
   *   The version of the API format.
   */
  public static function api() {
    return static::API_VERSION;
  }

}
