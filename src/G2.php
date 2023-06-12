<?php

namespace Drupal\g2;

/**
 * Class G2 is the container for general-use G2 data.
 */
class G2 {

  /**
   * The API format.
   */
  const API_VERSION = 8;

  // The name of the node.type.g2_entry config entity.
  const BUNDLE = 'g2_entry';

  /**
   * The config key for the module configuration.
   */
  const CONFIG_NAME = 'g2.settings';

  const DEFLOGREFERRERS = TRUE;

  const DEFNOFREETAGGING = FALSE;

  const DEFPAGETITLE = TRUE;

  const DEFPATHMAIN = 'g2.main';

  /**
   * The State default for the current stored random entry.
   */
  const DEFRANDOMENTRY = '';

  const DEFREMOTEG2 = FALSE;

  const DEFREMOTENO = '';

  const DEFTOOLTIPS = FALSE;

  const DEFTOPITEMCOUNT = 5;

  const DEFWOTDAUTOCHANGE = TRUE;

  /**
   * The config default for the WOTD entry.
   */
  const DEFWOTDENTRY = '';

  const DEFWOTDFEEDAUTHOR = '';

  const DEFWOTDFEEDDESCR = '';

  const DEFWOTDFEEDLINK = TRUE;

  const DEFWOTDTITLE = '';

  /**
   * Block: n most recent.
   */
  const DELTA_LATEST = 'latest';

  /**
   * Block: n most viewed.
   */
  const DELTA_TOP = 'top';

  /**
   * Block: word of the day.
   */
  const DELTA_WOTD = 'wotd';

  /**
   * The module name.
   *
   * Meant to be used to identify the origin of stored data emanating from this
   * module in another subsystem, e.g. logs, as happens with logger.channel.g2.
   */
  const NAME = 'g2';

  // Constants in this group are only listed to remove WSODs, but they still
  // need to be converting from hook_menu to Symfony routing.
  const PATH_ENTRIES = 'g2/entries';

  const PATH_INITIAL = 'g2/initial';

  const PATH_NODE_ADD = 'node/add/g2';

  const PATH_SETTINGS = 'g2/admin/settings';

  /**
   * The G2 permission for administrators.
   */
  const PERM_ADMIN = 'administer g2 entries';

  /**
   * Route: the core content administration route.
   */
  const ROUTE_ADMIN_CONTENT = 'system.admin_content';

  /**
   * Route: the G2 services configuration form.
   */
  const ROUTE_CONFIG_SERVICES = 'g2.settings.services';

  /**
   * Route: autocomplete by title.
   */
  const ROUTE_AUTOCOMPLETE = 'g2.autocomplete';

  /**
   * Route: The core block administration page.
   */
  const ROUTE_BLOCKS = 'block.admin_display';

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
   * Route: Field UI node display modes.
   */
  const ROUTE_VM = 'entity.entity_view_display.node.view_mode';

  /**
   * Route: form offering to wipe all referrers.
   */
  const ROUTE_WIPE_ALL = 'g2.wipe.all';

  /**
   * Route: form offering to wipe referrers for a single G2 entry.
   */
  const ROUTE_WIPE_ONE = 'g2.wipe.one';

  /**
   * The name of the g2.alphabar service.
   */
  const SVC_ALPHABAR = 'g2.alphabar';

  /**
   * The name of the core config.factory service.
   */
  const SVC_CONF = 'config.factory';

  /**
   * The name of the core entity_type.manager service.
   */
  const SVC_ETM = 'entity_type.manager';

  /**
   * The logger.channel.g2 service.
   */
  const SVC_LOGGER = 'logger.channel.g2';

  /**
   * The name of the g2.random service.
   */
  const SVC_RANDOM = 'g2.random';

  /**
   * The state service.
   */
  const SVC_STATE = 'state';

  /**
   * The name of the g2.test.logger service.
   *
   * Beware: this is NOT a Drupal logger channel, just a PSR-3 test helper.
   */
  const SVC_TEST_LOGGER = 'g2.test.logger';

  /**
   * The name of the g2.wotd service.
   */
  const SVC_WOTD = 'g2.wotd';

  const TITLE_MAIN = 'Glossary';

  /**
   * In this version, G2 entries are a node bundle (content type).
   *
   * This is likely to change as some point in the future.
   */
  const TYPE = 'node';

  /**
   * The config path for the route service the "entries by initial" page.
   */
  const VARCONTROLLERINITIAL = 'controller.initial.route';

  /**
   * The config path for the alphabar contents.
   */
  const VARALPHABARCONTENTS = 'services.alphabar.contents';

  /**
   * The config path for the alphabar visual row length.
   */
  const VARALPHABARROWLENGTH = 'block.alphabar.row_length';

  const VARLOGREFERRERS = 'g2-log-referrers';

  // Constants in this group are only listed to remove WSODs, but they still
  // need the associated logic to be converted from variables to config.
  const VARNOFREETAGGING = 'g2-no-freetagging';

  const VARPAGETITLE = 'g2-page-title';

  const VARPATHMAIN = 'g2-path-main';

  /**
   * The State key for the current stored random entry.
   */
  const VARRANDOMENTRY = 'g2.random-entry';

  /**
   * The config path for the random entry storage choice.
   */
  const VARRANDOMSTORE = 'services.random.store';

  const VARREMOTEG2 = 'g2-remote';

  const VARTOOLTIPS = 'g2-tooltips';

  const VARTOPITEMCOUNT = 'g2-top-item-count';

  /**
   * The config path for the WOTD auto_change property.
   */
  const VARWOTDAUTOCHANGE = 'services.wotd.auto_change';

  /**
   * The state path for the WOTD auto_change date.
   *
   * The associated value is a date("Y-m-d").
   */
  const VARWOTDDATE = 'g2.wotd-date';

  /**
   * The config path for the WOTD entry.
   */
  const VARWOTDENTRY = 'services.wotd.entry';

  const VARWOTDFEEDAUTHOR = 'g2-wotd-feed-author';

  const VARWOTDFEEDDESCR = 'g2-wotd-feed-description';

  const VARWOTDFEEDLINK = 'g2-wotd-feed-link';

  /**
   * Default is DEFWOTDTITLE, Use g2.settings.controller.wotd.title.
   */
  const VARWOTDFEEDTITLE = 'g2-wotd-feed-title';

  const VARWOTDTITLE = 'g2-wotd-title';

  /**
   * The public-facing version: two first levels for semantic versioning.
   */
  const VERSION = '8.1';

  // View modes.
  const VM_BLOCK = 'g2_block';

  const VM_ENTRY_LIST = 'g2_entry_list';

  const VM_HOMONYMS_PAGE = 'g2_homonyms_page';

  /**
   * Return the API version.
   *
   * @return int
   *   The version of the API format.
   */
  public static function api() {
    return static::API_VERSION;
  }

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
  public static function encodeTerminal($terminal): string {
    $terminal = strtr($terminal, [
      '.' => '%2E',
      '/' => '%2F',
      '#' => '%23',
      '&' => '%26',
      '+' => '%2B',
    ]);
    return $terminal;
  }

}
