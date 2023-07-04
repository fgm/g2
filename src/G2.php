<?php

declare(strict_types = 1);

namespace Drupal\g2;

use Drupal\Core\Cache\Cache;

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

  /**
   * The State default for the current stored random entry.
   */
  const DEFRANDOMENTRY = '';

  const DEFWOTDFEEDLINK = TRUE;

  /**
   * Block: n most viewed.
   */
  const DELTA_TOP = 'top';

  /**
   * Block: word of the day.
   */
  const DELTA_WOTD = 'wotd';

  /**
   * The key for the titles MSM in the Indexer KV collection.
   */
  const KV_MSM = 'g2:msm';

  /**
   * The key for the titles map in the Indexer KV collection.
   */
  const KV_TITLES = 'g2:titles';

  /**
   * The name of the G2 admin front library.
   */
  const LIB_ADMIN = self::NAME . '/g2.admin';

  /**
   * The name of the G2 alphabar styling library.
   */
  const LIB_ALPHABAR = self::NAME . '/' . self::SVC_ALPHABAR;

  /**
   * The module name.
   *
   * Meant to be used to identify the origin of stored data emanating from this
   * module in another subsystem, e.g. logs, as happens with logger.channel.g2.
   */
  const NAME = 'g2';

  // Constants in this group are only listed to remove WSODs, but they still
  // need to be converting from hook_menu to Symfony routing.
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
   * Route: autocomplete G2 entry by title.
   */
  const ROUTE_AUTOCOMPLETE_ENTRY = 'g2.autocomplete.entry';

  /**
   * Route: autocomplete route by name, only listing routes without parameters.
   */
  const ROUTE_AUTOCOMPLETE_ROUTE_0PARAM = 'g2.autocomplete.route_0param';

  /**
   * Route: autocomplete route by name, only listing routes with one parameter.
   */
  const ROUTE_AUTOCOMPLETE_ROUTE_1PARAM = 'g2.autocomplete.route_1param';

  /**
   * Route: the G2 API configuration form.
   */
  const ROUTE_CONFIG_API = 'g2.settings.api';

  /**
   * Route: the G2 controllers configuration form.
   */
  const ROUTE_CONFIG_CONTROLLERS = 'g2.settings.controllers';

  /**
   * Route: the G2 formatting configuration form.
   */
  const ROUTE_CONFIG_FORMATTING = 'g2.settings.formatting';

  /**
   * Route: the G2 services configuration form.
   */
  const ROUTE_CONFIG_SERVICES = 'g2.settings.services';

  /**
   * Route: The core block administration page.
   */
  const ROUTE_BLOCKS = 'block.admin_display';

  /**
   * Route: expose the entries with a given title.
   */
  const ROUTE_HOMONYMS = 'g2.homonyms';

  /**
   * Route: WOTD RSS feed.
   */
  const ROUTE_FEED_WOTD = 'view.g2_wotd.feed_1';

  /**
   * Route: G2 main page.
   */
  const ROUTE_MAIN = 'g2.main';

  /**
   * The name of the canonical node route.
   */
  const ROUTE_NODE_CANONICAL = 'entity.node.canonical';

  /**
   * Route: show referers to a g2_entry.
   */
  const ROUTE_REFERERS = 'g2.node_referers';

  /**
   * Route: Field UI node display modes.
   */
  const ROUTE_VM = 'entity.entity_view_display.node.view_mode';

  /**
   * Route: form offering to wipe all referers.
   */
  const ROUTE_WIPE_ALL = 'g2.wipe.all';

  /**
   * Route: form offering to wipe referers for a single G2 entry.
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
   * The name of the G2 matcher service.
   */
  const SVC_MATCHER = 'g2.matcher';

  /**
   * The name of the g2.latest service.
   */
  const SVC_LATEST = 'g2.latest';

  /**
   * The logger.channel.g2 service.
   */
  const SVC_LOGGER = 'logger.channel.g2';

  /**
   * The name of the g2.random service.
   */
  const SVC_RANDOM = 'g2.random';

  /**
   * The name of the g2.route_filter service.
   */
  const SVC_ROUTE_FILTER = 'g2.route_filter';

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
   * The name of the g2.top service.
   */
  const SVC_TOP = 'g2.top';

  /**
   * The name of the g2.referer_tracker service.
   */
  const SVC_TRACKER = 'g2.referer_tracker';

  /**
   * The name of the g2.wotd service.
   */
  const SVC_WOTD = 'g2.wotd';

  /**
   * The name of the SQL table holding the G2 referer information.
   */
  const TBL_REFERER = 'g2_referer';

  const TITLE_MAIN = 'Glossary';

  const TOOLTIPS_NONE = 0;

  const TOOLTIPS_TITLES = 1;

  const TOOLTIPS_TEASERS = 2;

  /**
   * In this version, G2 entries are a node bundle (content type).
   *
   * This is likely to change as some point in the future.
   */
  const TYPE = 'node';

  /**
   * The config path for the XML-RPC API server enabling.
   */
  const VARAPIENABLED = 'api.server.enabled';

  /**
   * The config path for the api throttle limiter.
   */
  const VARAPITHROTTLE = 'api.server.throttle';

  /**
   * The config path for the route service the "entries by initial" page.
   */
  const VARCONTROLLERINITIAL = 'controller.initial.route';

  const VARCONTROLLERHOMONYMS = 'controller.homonyms';

  /**
   * The config path for the alphabar contents.
   */
  const VARALPHABARCONTENTS = 'services.alphabar.contents';

  /**
   * The config path for the alphabar visual row length.
   */
  const VARALPHABARROWLENGTH = 'block.alphabar.row_length';

  /**
   * The config path for the homonyms redirect on single match.
   */
  const VARHOMONYMSREDIRECTSINGLE = 'controller.homonyms.redirect_on_single_match';

  /**
   * The config path for the homonyms listing node.
   *
   * @deprecated in drupal:8.1.0 and is removed from drupal:8.2.0. Use a view.
   * @see https://www.drupal.org/project/g2/issues/3369887
   */
  const VARHOMONYMSNID = 'controller.homonyms.nid';

  /**
   * The config path for the homonyms page route.
   */
  const VARHOMONYMSROUTE = 'controller.homonyms.route';

  /**
   * The config path for the homonyms listing view.
   */
  const VARHOMONYMSVID = 'controller.homonyms.vid';

  /**
   * The config path for the homonyms redirect HTTP status.
   */
  const VARHOMONYMSREDIRECTSTATUS = 'controller.homonyms.redirect_status';

  /**
   * The config path for the Latest(n) block entry count.
   */
  const VARLATESTCOUNT = 'block.latest.count';

  /**
   * The config path for the Latest(n) service maximum entry count.
   */
  const VARLATESTMAXCOUNT = 'services.latest.max_count';

  /**
   * The config path for the referrer logging feature.
   */
  const VARLOGREFERERS = 'controller.referers.track';

  /**
   * The config path for the node used to build the main G2 page.
   *
   * @deprecated in drupal:8.1.0 and is removed from drupal:8.2.0. Use a view.
   * @see https://www.drupal.org/project/g2/issues/3369887
   */
  const VARMAINNID = 'controller.main.nid';

  /**
   * The config path for the main page route.
   *
   * Always use it to build URLs instead of assuming route g2.main.
   */
  const VARMAINROUTE = 'controller.main.route';

  /**
   * The config path for the free tagging choice on node formatting.
   */
  const VARNOFREETAGGING = 'formatting.hide_free_tagging';

  /**
   * The config path for the title override on G2 entry pages.
   */
  const VARPAGETITLE = 'formatting.title';

  /**
   * The State key for the current stored random entry.
   */
  const VARRANDOMENTRY = 'g2.random-entry';

  /**
   * The config path for the random entry storage choice.
   */
  const VARRANDOMSTORE = 'services.random.store';

  /**
   * The config path for the URL of the remote glossary server.
   */
  const VARREMOTEG2 = 'api.client.remote';

  /**
   * The config path for the level of tooltips features.
   */
  const VARTOOLTIPS = 'formatting.tooltips_level';

  /**
   * The config path for the Top(n) block entry count.
   */
  const VARTOPCOUNT = 'block.top.count';

  /**
   * The config path for the Top(n) service maximum entry count.
   */
  const VARTOPMAXCOUNT = 'services.top.max_count';

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

  const VARWOTDFEEDLINK = 'g2-wotd-feed-link';

  /**
   * The public-facing version: two first levels for semantic versioning.
   */
  const VERSION = '8.1';

  /**
   * The ID of the Authored view.
   */
  const VIEW_AUTHORED = 'g2_authored';

  /**
   * The ID of the block display in the Authored view.
   */
  const VIEW_AUTHORED_DISPLAY = 'user_page_block';

  /**
   * The ID of the WOTD view.
   */
  const VIEW_WOTD = 'g2_wotd';

  /**
   * The ID of the feed display in the WOTD view.
   */
  const VIEW_WOTD_DISPLAY = 'feed_1';

  // View modes.
  const VM_BLOCK = 'g2_block';

  /**
   * The view display used on the homonyms "plain node list" page.
   */
  const VM_ENTRY_LIST = 'g2_entry_list';

  /**
   * The view display used on the deprecated homonyms node-based page.
   *
   * @deprecated in drupal:8.1.0 and is removed from drupal:8.2.0. Use a view.
   * @see https://www.drupal.org/project/g2/issues/3369887
   */
  const VM_HOMONYMS_PAGE = 'g2_homonyms_page';

  /**
   * The core RSS view display.
   */
  const VM_RSS = 'rss';

  /**
   * The view display used for tooltips on definition links.
   */
  const VM_TOOLTIPS = 'g2_tooltips';

  /**
   * Return the API version.
   *
   * @return int
   *   The version of the API format.
   */
  public static function api(): int {
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
  public static function encodeTerminal(string $terminal): string {
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
   * Manually invalidate the WOTD view.
   *
   * This is needed because of a core bug causing cache metadata from default
   * argument plugins not to be present in Views results.
   *
   * To work around the missing automatic invalidation, we invalidate the
   * rendered elements including the WOTD view manually.
   *
   * @see https://www.drupal.org/project/drupal/issues/3371236
   */
  public static function invalidateWotdView(): void {
    Cache::invalidateTags(["config:views.view.g2_wotd"]);
  }

}
