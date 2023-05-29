<?php

namespace Drupal\g2;

/**
 * Class G2 is the container for general-use G2 data.
 */
class G2 {
  // The name of the node.type.g2_entry config entity.
  const BUNDLE = 'g2_entry';

  /**
   * The key for the module configuration.
   */
  const CONFIG_NAME = 'g2.settings';

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

  /**
   * In this version, G2 entries are a node bundle (content type).
   *
   * This is likely to change as some point in the future.
   */
  const TYPE = 'node';

  /**
   * Route: autocomplete by title.
   */
  const ROUTE_AUTOCOMPLETE = 'g2.autocomplete';

  /**
   * Route: WOTD RSS feed.
   */
  const ROUTE_FEED_WOTD = 'g2.feed.wotd';

  /**
   * Route: G2 main page.
   */
  const ROUTE_MAIN = 'g2.main';

  /**
   * Route: show referrers to a g2_entry.
   */
  const ROUTE_REFERRERS = 'g2.node_referrers';

  /**
   * Route: form offering to wipe all referrers.
   */
  const ROUTE_WIPE_ALL = 'g2.wipe.all';

  /**
   * Route: form offering to wipe referrers for a single G2 entry.
   */
  const ROUTE_WIPE_ONE = 'g2.wipe.one';


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
  // Use g2.settings.controller.wotd.title.
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
  const PATH_ENTRIES = 'g2/entries';
  const PATH_INITIAL = 'g2/initial';
  const PATH_NODE_ADD = 'node/add/g2';
  const PATH_SETTINGS = 'g2/admin/settings';

  const TITLE_MAIN = 'Glossary';

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
