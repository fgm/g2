<?php
/**
 * @file
 * Non-hook code for the G2 module.
 *
 * This file is split off the main g2.module file, although it is always
 * included, because coder.module does not recognize proper comment indentation
 * within namespace {} blocks.
 *
 * (C) 2005-2011 Frederic G. MARAND for Ouest Systèmes Informatiques (OSInet)
 *
 * Licensed under the CeCILL, version 2 and General Public License version 2 or
 * later.
 *
 * License note: G2 is distributed by OSInet to its customers under the
 * CeCILL 2.0 license. OSInet support services only apply to the module
 * when distributed by OSInet, not by any third-party further down the
 * distribution chain.
 *
 * If you obtained G2 from drupal.org, that site received it under the General
 * Public License version 2 or later (GPLv2+) and can therefore distribute it
 * under the same terms, and so can you and just anyone down the chain as long
 * as the GPLv2+ terms are abided by, the module distributor in that case being
 * the drupal.org organization or the downstream distributor, not OSInet.
 */

namespace G2 {
  /* ==== Default values for the persistent variables ======================= */

  /**
   * Default value of the G2 alphabar
   */
  const DEFALPHABAR = '0123456789abcdefghijklmnopqrstuvwxyz';

  /**
   * Default length of an alphabar row
   */
  const DEFALPHABARROWLEN = 13;

  /**
   * Does the webmaster want to jump to single match on entries page ?
   */
  const DEFGOTOSINGLE = TRUE;

  /**
   * Default for "hidden extra title" on node views.
   */
  const DEFHIDDENTITLE = TRUE;

  /**
   * The default nid for the homonyms disambiguation page
   */
  const DEFHOMONYMS = 0;

  /**
   * Default value of the G2 HTTP automatic redirect code
   */
  const DEFHOMONYMSREDIRECT = '302';

  /**
   * The default vocabulary id for the homonyms disambiguation page
   */
  const DEFHOMONYMSVID = 0;

  /**
   * Default value for the number of entries in latest(n) lists
   */
  const DEFLATESTITEMCOUNT = 10;

  /**
   * Default value for G2 referrer logging
   */
  const DEFLOGREFERRERS = TRUE;

  /**
   * Nid of the unpublished node used as main page for G2: none
   */
  const DEFMAIN = 0;

  /**
   * Default G2 main path
   */
  const DEFPATHMAIN = 'g2';

  /**
   * Default G2 "random" entry: none
   */
  const DEFRANDOMENTRY = '';

  /**
   * Default G2 "store random value" setting.
   *
   * This choice is best for smaller sites, but should usually be overriden in
   * settings for larger sites.
   */
  const DEFRANDOMSTORE = TRUE;

  /**
   * Default G2 "show terms on random node" setting
   */
  const DEFRANDOMTERMS = FALSE;

  /**
   * Default G2 remote glossary
   */
  const DEFREMOTEG2 = 'http://www.riff.org/g2/entries';

  /**
   * Default G2 non-remote path
   */
  const DEFREMOTENO = '<local>';

  /**
   * Default G2 RPC throttle value
   */
  const DEFRPCTHROTTLE = 10;

  /**
   * Default G2 tooltips level
   */
  const DEFTOOLTIPS = 0;

  /**
   * G2 Top item count
   */
  const DEFTOPITEMCOUNT = 10;

  /**
   * Automatically change the WOTD every day ?
   */
  const DEFWOTDAUTOCHANGE = TRUE;

  /**
   * Default G2 WOTD body size
   */
  const DEFWOTDBODYSIZE = 40;

  /**
   * Default G2 WOTD entry: none
   */
  const DEFWOTDENTRY = 0;

  /**
   * Default G2 author in feeds
   */
  const DEFWOTDFEEDAUTHOR = "@author";

  /**
   * Include link on G2 WOTD block
   */
  const DEFWOTDFEEDLINK = TRUE;

  /**
   * WOTD block: do not show terms bound to entry
   */
  const DEFWOTDTERMS = FALSE;

  /**
   * Default value for XML-RPC enabling
   */
  const DEFXMLRPC = FALSE;

  /* ==== Block deltas ====================================================== */
  /**
   * Block: alphabar
   */
  const DELTAALPHABAR = 'alphabar';

  /**
   * Block: n most recent
   */
  const DELTALATEST = 'latest';

  /**
   * Block: random
   */
  const DELTARANDOM = 'random';

  /**
   * Block: n most viewed
   */
  const DELTATOP = 'top';

  /**
   * Block: word of the day
   */
  const DELTAWOTD = 'wotd';

  /* ==== Miscellaneous constants =========================================== */

  /**
   * G2 builtin node type
   */
  const NODETYPE = 'g2_entry';

  /**
   * G2 permission for normal users
   */
  const PERMVIEW = 'view g2 entries';

  /**
   * G2 permission for administrators
   */
  const PERMADMIN = 'administer g2 entries';

  /**
   * G2 module version
   */

  const VERSION = '7.x-1.x';


  /* ==== Hook_menu paths =================================================== */

  /**
   * G2 node reference path
   */
  const PATHAUTOCOMPLETE = 'g2/autocomplete';

  /**
   * G2 path for pages listing entries
   */
  const PATHENTRIES = 'g2/entries';

  /**
   * G2 path for by-initial pages
   */
  const PATHINITIAL = 'g2/initial';

  /**
   * G2 path for the G2 node creation page
   */
  const PATHNODEADD = 'node/add/g2_entry';

  /**
   * G2 admin UI path
   */
  const PATHSETTINGS              = 'admin/config/content/g2';

  /**
   * G2 path for the WOTD feed
   */
  const PATHWOTDFEED = 'g2/wotd/feed';


  /* ==== Page and feed titles (not translated) ============================= */
  /**
   * Title of the G2 pages listing entries
   */
  const TITLEENTRIES = 'G2 entries by name';

  /**
   * Title of the G2 by-initial pages
   */
  const TITLEINITIAL = 'entries starting with initial %initial';

  /**
   * Title of the G2 main page
   */
  const TITLEMAIN = 'G2 glossary main page';

  /**
   * Title of the referer wipeout page, and associated submit button
   */
  const TITLEREFERERWIPE = 'Wipe all G2 referer information';

  /**
   * Title of the G2 WOTD feed
   */
  const TITLEWOTDFEED = 'G2 word of the day RSS feed';


  /* ==== Persistent variables ============================================== */

  /**
   * Alphabar block: list of the one-symbol initials being displayed
   * @ingroup g2vars
   */
  const VARALPHABAR = 'g2_alphabar';

  /**
   * Alphabar block: row length of the themed alphabar
   * @ingroup g2vars
   */
  const VARALPHABARROWLEN = 'g2_alphabar_rowlen';

  /**
   * Automatically go to the match on an "entries" page if only one exists
   * @ingroup g2vars
   */
  const VARGOTOSINGLE = 'g2_goto_single';

  /**
   * Does node view include a hidden version of title for h._update_index ?
   * @ingroup g2vars
   */
  const VARHIDDENTITLE = 'g2_hidden_title';

  /**
   * Disambiguation page for homonyms
   * @ingroup g2vars
   */
  const VARHOMONYMS = 'g2_homonyms';

  /**
   * The HTTP 30x code to be used for automatic redirects
   * @ingroup g2vars
   */
  const VARHOMONYMSREDIRECT = 'g2_homonyms_redirect';

  /**
   * The disambiguation vocabulary id for homonyms
   * @ingroup g2vars
   */
  const VARHOMONYMSVID = 'g2_homonyms_vid';

  /**
   * Latest(x) block: value of x
   * @ingroup g2vars
   */
  const VARLATESTITEMCOUNT = 'g2_latest_item_count';


  /**
   * Log referrers on G2 node views.
   * @ingroup g2vars
   */
  const VARLOGREFERRERS = 'g2_log_referrers';

  /**
   * Nid of the unpublished node used as main page for G2
   * @ingroup g2vars
   */
  const VARMAIN = 'g2_main';

  /**
   * Override for default site title on G2 pages
   * @ingroup g2vars
   */
  const VARPAGETITLE = 'g2_page_title';

  /**
   * Path for the G2 main page
   * @ingroup g2vars
   */
  const VARPATHMAIN = 'g2_pathmain';

  /**
   * Random block: latest pseudo-random entry displayed
   * @ingroup g2vars
   */
  const VARRANDOMENTRY = 'g2_random_entry';

  /**
   * Random block: store the latest random entry
   * @ingroup g2vars
   */
  const VARRANDOMSTORE = 'g2_random_store';

  /**
   * Random block: show terms bound to entry
   * @ingroup g2vars
   */
  const VARRANDOMTERMS = 'g2_random_terms';

  /**
   * Base URL of remote G2 instance
   * @ingroup g2vars
   */
  const VARREMOTEG2 = 'g2_remote_g2';

  /**
   * Coefficient limit for XML-RPC operations over block settings
   * @ingroup g2vars
   */
  const VARRPCTHROTTLE = 'g2_rpc_throttle';

  /**
   * Tooltips enabled on definitions ?
   * @ingroup g2vars
   */
  const VARTOOLTIPS = 'g2_tooltips';

  /**
   * WOTD block: automatically change the WOTD once a day
   * @ingroup g2vars
   */
  const VARWOTDAUTOCHANGE = 'g2_wotd_autochange';

  /**
   * WOTD block: number of body characters to be displayed in the block
   * @ingroup g2vars
   */
  const VARWOTDBODYSIZE = 'g2_wotd_bodysize';

  /**
   * WOTD block: date for which this WOTD entry is current *
   * @ingroup g2vars
   */
  const VARWOTDDATE = 'g2_wotd_date';

  /**
   * WOTD block: current entry
   * @ingroup g2vars
   */
  const VARWOTDENTRY = 'g2_wotd_entry';

  /**
   * WOTD block feed: include a link to the WOTD RSS feed in the block
   * @ingroup g2vars
   */
  const VARWOTDFEEDLINK = 'g2_wotd_feed_link';

  /**
   * WOTD block feed: the title for the WOTD RSS feed
   * @ingroup g2vars
   */
  const VARWOTDFEEDTITLE = 'g2_wotd_feed_title';

  /**
   * WOTD block feed: the description for the WOTD RSS feed
   * @ingroup g2vars
   */
  const VARWOTDFEEDDESCR = 'g2_wotd_feed_descr';

  /**
   * WOTD block feed: include the author in the feed entries
   * @ingroup g2vars
   */
  const VARWOTDFEEDAUTHOR = 'g2_wotd_feed_author';

  /**
   * WOTD block: show terms bound to entry
   * @ingroup g2vars
   */
  const VARWOTDTERMS = 'g2_wotd_terms';

  /**
   * WOTD block: the title for the block
   * @ingroup g2vars
   */
  const VARWOTDTITLE = 'g2_wotd_title';

  /**
   * Top(x) block: value of x
   * @ingroup g2vars
   */
  const VARTOPITEMCOUNT = 'g2_top_item_count';

  /**
   * Is the XML-RPC server enabled ?
   * @ingroup g2vars
   */
  const VARXMLRPC = 'g2_xmlrpc';

  /**
   * Form builder for settings form.
   *
   * @param array $form
   *   The form array
   * @param array $form_state
   *   The form state
   *
   * @return array
   *   The completed form.
   */
  function admin_settings($form, &$form_state) {
    // Activate context.
    if ($plugin = context_get_plugin('condition', 'g2')) {
      $plugin->execute('g2_admin');
    }

    $form['main'] = array(
      '#type' => 'fieldset',
      '#title' => t('Main page'),
    );
    $form['main'][VARMAIN] = array(
      '#type' => 'textfield',
      '#title' => t('Node used for the main page of G2 on this site'),
      '#default_value' => variable_get(VARMAIN, 0),
      '#description' => t('<p>This value can be a string or an integer.</p>
<ul>
  <li>If it is a string, it has to be the name of a function returning a render array.</li>
  <li>If it is a node number, the node will be used in full view to build the page.</li>
  </ul>
<p>The default g2_main template will build the the page as a basis, wrapping it between alphabars.</p>'),
    );
    $form['main'][VARPATHMAIN] = array(
      '#type' => 'textfield',
      '#title' => t('Path for the main page of G2 on this site'),
      '#default_value' => variable_get(VARPATHMAIN, DEFPATHMAIN),
      '#description' => t('<p>This setting allows site admins to modify the URL at which the home page will be found.
           This will typically be done to replace the standard path ("g2") by a SEO-engineered path.</p>'),
    );
    $form['main'][VARPAGETITLE] = array(
      '#type' => 'textfield',
      '#title' => t('Override for site title on G2 pages'),
      '#default_value' => variable_get(VARPAGETITLE, t('G2 Glossary on @title')),
      '#description' => t('<p>This setting allows site admins to modify the site title on G2 page, typically for SEO purposes. The title can include !token, which will be replaced by the standard Drupal site title. If set to empty, the default site name will not be overriden.</p>',
        array('!token' => '@title')),
    );
    $form['main'][VARTOOLTIPS] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable tooltips on definitions'),
      '#default_value' => variable_get(VARTOOLTIPS, DEFTOOLTIPS),
      '#description' => t('Set the title attribute on definition links to the teaser of the target definition, if only one exists, or to the number of definitions, if several exist. This mechanism is only enabled on non-remote glossaries, and may cause one additional DB query per definition link on pages containg them.'),
    );

    $form['automation'] = array(
      '#type' => 'fieldset',
      '#title' => t('Site automation'),
    );
    $form['automation'][VARHIDDENTITLE] = array(
      '#type' => 'checkbox',
      '#title' => t('Embed a CSS-masked version of the definition titles in the node rendition'),
      '#default_value' => variable_get(VARHIDDENTITLE, DEFHIDDENTITLE),
      '#description' => t('This setting enables search to work better on definition titles, but can be frowned upon by search engines.'),
    );
    $form['automation'][VARLOGREFERRERS] = array(
      '#type' => 'checkbox',
      '#title' => t('Log referrers on node views.'),
      '#default_value' => variable_get(VARLOGREFERRERS, TRUE),
      '#description' => t('This can provide hints regarding pages referring to definitions.'),
    );
    $form['remoting'] = array(
      '#type' => 'fieldset',
      '#title' => t('Remoting'),
    );
    $def = variable_get(VARREMOTEG2, DEFREMOTEG2);
    if (empty($def)) {
      $def = DEFREMOTEG2;
    }
    $form['remoting'][VARREMOTEG2] = array(
      '#type' => 'textfield',
      '#title' => t('Use the URL of this remote G2 glossary, or !local for a local glossary',
        array(
        '!local' => '<code>' . check_plain(DEFREMOTENO) . '</code>',
      )
      ),
      '#default_value' => $def,
      '#description' => t('Using G2 remoting, you can supply automatic links from your site to a glossary on another site, without having to maintain anything locally. You will just need to enable the "G2 Glossary Filter" at !url for each format using it. Use !local to maintain a glossary on your site. Erase the field and save TWICE to restore the default value, the Riff dictionary of computing.',
        array(
        '!url' => l(t('input formats'), 'admin/config/content/formats'),
        '!local' => '<code>' . check_plain(DEFREMOTENO) . '</code>',
      )
      ),
    );
    $form['homonyms'] = array(
      '#type' => 'fieldset',
      '#title' => t('Homonyms processing'),
    );
    $disambiguation_url = url('g2/entries', array('absolute' => TRUE));
    $form['homonyms'][VARHOMONYMS] = array(
      '#type' => 'textfield',
      '#title' => t('Node used for the homonyms disambiguation page of G2 on this site'),
      '#default_value' => variable_get(VARHOMONYMS, DEFHOMONYMS),
      '#description' => t('If this node is non-zero, this is the page used to build the disambiguation
            page for G2 entries at <code>!link/&lt;some entry&gt;</code>.
            Otherwise, a default page is used.', array('!link' => $disambiguation_url)),
    );
    $form['homonyms'][VARGOTOSINGLE] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable auto-jump to single match on entry'),
      '#default_value' => variable_get(VARGOTOSINGLE, TRUE),
      '#description' => t('This setting enables automatic redirection from
        <code>!link/&lt;some entry&gt;</code> to the entry page if only one match exists for "some entry".
        This is especially useful for links generated from content nodes.',
        array('!link' => $disambiguation_url)),
    );
    $form['homonyms'][VARHOMONYMSREDIRECT] = array(
      '#type' => 'select',
      '#title' => t('The type of HTTP redirection to be used if auto-jump is enabled'),
      '#default_value' => variable_get(VARHOMONYMSREDIRECT, DEFHOMONYMSREDIRECT),
      '#options' => array(
        '301' => t('Permanent redirect (HTTP 301)'),
        DEFHOMONYMSREDIRECT => t('Temporary redirect (HTTP 302)'),
      ),
      '#description' => t('If the "auto-jump to single match on entry" feature is enabled,
        choose which type of redirection is generated. Technically, a temporary redirect (!see302)
        makes more sense, especially is the glossary is often updated, but if the glossary
        is stable, or if the site admin has reason to fear a 302 search engine blacklisting,
        this setting makes it possible to use a permanent redirect (!see301).',
        array(
        '!see302' => '<a href="http://tools.ietf.org/html/rfc2616#section-10.3.3">RFC 2616 §10.3.3</a>',
        '!see301' => '<a href="http://tools.ietf.org/html/rfc2616#section-10.3.2">RFC 2616 §10.3.2</a>',
        )
      ),
    );

    $ar_voc = array('0' => t('None'));
    foreach (taxonomy_get_vocabularies(NODETYPE) as $voc) {
      $ar_voc[$voc->vid] = $voc->name;
    }
    $form['homonyms'][VARHOMONYMSVID] = array(
      '#type' => 'select',
      '#title' => t('Vocabulary to be used on the homonyms disambiguation page'),
      '#default_value' => variable_get(VARHOMONYMSVID, DEFHOMONYMSVID),
      '#description' => t('If a vocabulary is chosen here, the terms bound to ambiguous entries (homonyms)
         will be displayed on the defaut disambiguation page.'),
      '#options' => $ar_voc,
    );

    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced settings (for programmers only)'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['advanced'][VARXMLRPC] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable G2 remote XML-RPC services'),
      '#default_value' => variable_get(VARXMLRPC, DEFXMLRPC),
      '#description' => t('This setting enables remote anonymous use of the G2 glossary through its XML-RPC services.
         Enabling it without additional access control is typically not a good idea.
         Local XML-RPC is not enabled by default.'),
    );

    $form['advanced'][VARRPCTHROTTLE] = array(
      '#type' => 'textfield',
      '#title' => t('RPC throttle'),
      '#default_value' => variable_get(VARRPCTHROTTLE, DEFRPCTHROTTLE),
      '#description' => t('This setting is a coefficient applied to the Top and Latest block for their XML-RPC services.
          It defines the maximum number of entries an XML-RPC client is allowed to return
          in just one call as the product of the block setting and this coefficient.'),
    );
    $form['advanced']['version'] = array(
      '#markup' => '<p>'
        . t('This site is running G2 version %version. See the <a href="!page" title="G2 Glossary project page">G2 project page</a> on Drupal.org.',
          array(
        '%version' => VERSION,
        '!page' => 'http://drupal.org/project/g2',
      )
        )
        . '</p>',
    );

    $form['wipe_submit'] = array(
      '#type' => 'submit',
      '#value' => TITLEREFERERWIPE,
      '#weight' => 2,
      '#submit' => array('G2\referer_wipe_button_submit'),
    );

    // Explicit handler needed: the explicit submit handler declaration in
    // system_settings_form() prevents implicit handlers from running.
    $form['#submit'] = array('G2\admin_settings_submit');
    $ret = system_settings_form($form);
    return $ret;
  }

  /**
   * Rebuild menu if the G2 main page changed.
   *
   * This is needed because cache_clear_all() in
   * system_settings_form_submit() only clears {cache_block} and
   * {cache_page}.
   *
   * @param array $form
   *   The form array.
   * @param array $form_state
   *   The current form state.
   */
  function admin_settings_submit(&$form, &$form_state) {
    if ($form_state['values'][VARPATHMAIN] != variable_get(VARPATHMAIN, DEFPATHMAIN)) {
      variable_set('menu_rebuild_needed', TRUE);
    }
  }

  /**
   * Make sure the node chosen for the main page is valid.
   *
   * @param array $form
   *   The form array.
   * @param array $form_state
   *   The current form state.
   */
  function admin_settings_validate(&$form, &$form_state) {
    $nid = $form_state['values']['g2_main'];
    if ($nid != 0 && !is_object(node_load($nid))) {
      form_set_error('g2_main', t('The node chosen for the main page must be a valid one, or 0: "@nid" is not a valid node id.',
        array('@nid' => $nid)));
    }

    $nid = $form_state['values']['g2_homonyms'];
    if ($nid != 0 && !is_object(node_load($nid))) {
      form_set_error('g2_homonyms', t('The node chosen for the homonyms disambiguation page must be a valid one, or 0: "@nid" is not a valid node id.',
        array('@nid' => $nid)));
    }

    // Sanitize RPC throttle.
    $form_state['values']['g2_rpc_throttle'] = (int) $form_state['values']['g2_rpc_throttle'];

    // Make sure tooltips are not enabled for remote glossaries.
    if ($form_state['values'][VARTOOLTIPS] && $form_state['values'][VARREMOTEG2] != DEFREMOTENO) {
      form_set_error(VARTOOLTIPS, t('Tooltips are only available on local glossaries'));
    }
  }

  /**
   * Return alphabar data.
   *
   * @return string
   *   The alphabar contents: a string made up of individual symbols, each
   *   wrapped in a link.
   */
  function alphabar() {
    $raw_alphabar = variable_get(VARALPHABAR, DEFALPHABAR);
    $ret = array();
    $options = array(
      // So alphabar can be used outside web pages.
      'absolute' => TRUE,
      // To preserve the pre-encoded path.
      'html' => TRUE,
    );
    for ($i = 0; $i < drupal_strlen($raw_alphabar); $i++) {
      $c = drupal_substr($raw_alphabar, $i, 1);
      $path = terminal_encode($c);
      $ret[] = l($c, PATHINITIAL . '/' . $path, $options);
    }
    return $ret;
  }

  /**
   * Return the current XML-RPC API version.
   *
   * @return int
   *   The major version of the API. No change since D6.
   */
  function api() {
    return 6;
  }

  /**
   * AJAX autocomplete for entry
   *
   * @see g2_menu()
   * @see g2_block()
   *
   * @param string $us_string
   *   The beginning of the entry.
   */
  function autocomplete($us_string = NULL) {
    $matches = array();
    if (isset($us_string)) {
      $us_string = drupal_strtolower($us_string);
      $sq = <<<EOT
SELECT n.nid, n.title, n.sticky
FROM {node} n
WHERE LOWER(n.title) LIKE '%s%%'
  AND n.type = '%s'
  AND (n.status = 1)
ORDER BY n.sticky DESC, LOWER(n.title) ASC
EOT;
      $q = db_select('node', 'n');
      $q->fields('n', array('nid', 'title', 'sticky'))
        ->where('LOWER(n.title) LIKE :title', array(':title' => $us_string . '%'))
        ->condition('n.type', NODETYPE)
        ->condition('n.status', 1)
        ->orderBy('n.sticky', 'DESC')
        ->orderBy('LOWER(n.title)')
        ->addTag('node_access')
        ->range(0, 10);
      $result = $q->execute();
      foreach ($result as $o) {
        $title = $o->sticky
          ? t('@title [@nid, sticky]', array('@title' => $o->title, '@nid' => $o->nid))
          : t('@title [@nid]',         array('@title' => $o->title, '@nid' => $o->nid));
        $matches[$title] = $o->title;
      }
    }
    drupal_json_output($matches);
    exit();
  }

  /**
   * Convert cache type constants to their names.
   *
   * @param int $mode
   *   Refer to cache constants in common.inc (but simplified)
   *
   * @return string
   *   A human-readable name.
   */
  function block_cache_decode($mode) {
    if ($mode == -1) {
      $ret = 'NO_CACHE';
    }
    elseif ($mode == -2) {
      $ret = 'CACHE_CUSTOM';
    }
    elseif ($mode < 0) {
      $ret = t('Inconsistent caching mode @mode', array('@mode' => sprintf('%04x', $mode)));
    }
    else {
      $ret = array();
      $bits = array(
        DRUPAL_CACHE_PER_ROLE => 'CACHE_PER_ROLE',
        DRUPAL_CACHE_PER_USER => 'CACHE_PER_USER',
        DRUPAL_CACHE_PER_PAGE => 'CACHE_PER_PAGE',
        DRUPAL_CACHE_GLOBAL => 'DRUPAL_CACHE_GLOBAL',
      );
      $remainder = $mode;
      foreach ($bits as $bit => $name) {
        if ($mode & $bit) {
          $ret[] = $name;
          $remainder &= !$bit;
        }
      }
      if ($remainder) {
        $ret[] = t('Extra bits: @bits', array('@bits' => sprintf('0x%04x', $remainder)));
      }
      $ret = empty($ret) ? t('Inconsistent: 0x0000') : implode(' | ', $ret);
    };
    return $ret;
  }

  /**
   * Prepare callback for filter_g2().
   *
   * @link http://drupal.org/node/267484 @endlink
   * @link http://drupal.org/node/209715 @endlink
   */
  function filter_prepare($text, $filter, $format, $langcode, $cache, $cache_id) {
    $text = preg_replace('@<dfn>(.+?)</dfn>@s', "[g2-dfn]\\1[/g2-dfn]", $text);
    return $text;
  }

  /**
   * Callback for G2\filter_process().
   *
   * Translate glossary linking elements (<dfn>) to actual links)
   *
   * This function generates absolute links, for the benefit of the WOTD RSS
   * feed. If this feed is not used, it is possible to use the (shorter)
   * relative URLs by swapping comments.
   *
   * @param string $entry
   *   An entry.
   *
   * @return string
   *   HTML: the processed contents of the entry.
   */
  function filter_process_callback($entry) {
    // Loop detection. Not using drupal_static() "if a function's static
    // variable does not depend on any information outside of the function that
    // might change during a single page request, then it's ok to use the
    // "static" keyword instead of the drupal_static() function.
    static $hits = array();

    // [0] is the original string.
    $entry = $entry[1];

    $target = variable_get(VARREMOTEG2, DEFREMOTEG2);

    // If we are not using a remote glossary.
    if ($target == DEFREMOTENO) {
      $target = PATHENTRIES;

      // No tooltips on remote glossaries: too slow.
      if (variable_get(VARTOOLTIPS, DEFTOOLTIPS)) {
        if (!isset($hits[$entry])) {
          // Prevent infinite loop from g2_title_load().
          $hits[$entry] = array();
          $hits[$entry] = g2_title_load($entry);
        }
        $count = count($hits[$entry]);
        if ($count == 1) {
          $node = reset($hits[$entry]);
          $tooltip = strip_tags($node->expansion);
        }
        elseif ($count) {
          $tooltip = t('@count entries for @entry', array(
            '@count' => $count,
            '@entry' => $entry,
          ));
        }
        else {
          $tooltip = t('No such term.');
        }
      }
    }
    else {
      $tooltip = NULL;
    }

    $path = urlencode(terminal_encode($entry));
    $attributes = array(
      'class' => 'g2-dfn-link',
    );
    if (isset($tooltip)) {
      $attributes['title'] = $tooltip;
    }

    $ret = l($entry, $target . '/' . $path, array(
      'absolute' => TRUE,
      'html' => FALSE,
      'attributes' => $attributes,
    ));
    return $ret;
  }

  /**
   * Process callback for filter_g2.
   *
   * @link http://drupal.org/node/267484 @endlink
   * @link http://drupal.org/node/209715 @endlink
   */
  function filter_process($text, $filter, $format, $langcode, $cache, $cache_id) {
    $text = preg_replace_callback('@\[g2-dfn\](.+?)\[/g2-dfn\]@s', 'G2\filter_process_callback', $text);
    return $text;
  }

  /**
   * Tips callback for filter_g2.
   */
  function filter_tips($delta, $format, $long = FALSE) {
    $ret = $long
      ? t('Wrap &lt;dfn&gt; elements around the terms for which you want a link to the available G2 definition(s).')
      : t('You may link to G2 definitions using &lt;dfn&gt; elements.');
    return $ret;
  }

  /**
   * Return a list of words starting with an initial segment.
   *
   * Segments are typically one letter, but can be any starting substring.
   *
   * The logic is different from the one in G2\entries() because we don't care
   * for the special case of "/" as an initial segment.
   *
   * XXX abstract to EntityFieldQuery
   *
   * @param string $initial
   *   Usually a single letter.
   *
   * @return string
   *   HTML
   */
  function initial($initial) {
    $initial = check_plain($initial);
    $ar_total   = stats();
    $ar_initial = stats(0, $initial);

    $ret = t("<p>Displaying @count entries starting with '%initial' from a total number of @total entries.</p>",
      array(
      // Since G2\stats() does not return empty arrays, no need to check values.
      '@count' => $ar_initial[NODE_PUBLISHED],
      '%initial' => $initial,
      '@total' => $ar_total  [NODE_PUBLISHED],
    )
    );

    if (user_access(PERMADMIN)) {
      $ret .= t('<p>Admin info: there are also @count unpublished matching entries from a total number of @total unpublished entries.</p>',
        array(
        '@count' => $ar_initial[NODE_NOT_PUBLISHED],
        '@total' => $ar_total  [NODE_NOT_PUBLISHED],
      )
      );
    }

    unset($ar_initial);
    unset($ar_total);

    $q = db_select('node', 'n');
    $q->innerJoin('node_revision', 'v', 'n.vid = v.vid');
    $q->fields('n', array('nid'))
      // ->fields('v', array('title'))
      ->condition('n.status', 1)
      ->condition('n.type', NODETYPE)
      ->condition('v.title', $initial . '%', 'LIKE')
      ->orderBy('v.title')
      ->addTag('node_access');

    $nids = array();
    $result = $q->execute();
    foreach ($result as $row) {
      $nids[] = $row->nid;
    }

    $ret = node_view_multiple(node_load_multiple($nids), 'g2_entry_list');
    return $ret;
  }

  /**
   * Returns a list of the latest n nodes.
   *
   * "Latest" nodes are identified by time of latest update.
   *
   * @param int $max
   *   The maximum number of entries to return.
   * @param boolean $include_unpublished
   *   Include unpublished nodes in that list.
   *
   * @return array
   *   Note that the results are NOT filtered, and must be filtered when used.
   */
  function latest($max = NULL, $include_unpublished = FALSE) {
    $def_max      = variable_get(VARLATESTITEMCOUNT, DEFLATESTITEMCOUNT);
    $rpc_throttle = variable_get(VARRPCTHROTTLE,     DEFRPCTHROTTLE);

    // Limit extraction.
    if (empty($max) || ($max > $rpc_throttle * $def_max)) {
      $max = $def_max;
    }

    // Only list unpublished nodes if requested and allowed.
    $status = ($include_unpublished && user_access('administer nodes'))
      ? NODE_NOT_PUBLISHED
      : NODE_PUBLISHED;

    $q = db_select('node', 'n');
    $q->fields('n', array('nid', 'title', 'status', 'type'))
      ->condition('n.type', NODETYPE)
      ->condition('n.status', $status, '>=')
      ->orderBy('n.changed', 'DESC')
      ->addTag('node_access')
      ->range(0, $max);
    // dsm($q->__toString());
    $result = $q->execute();

    $ret = array();
    foreach ($result as $row) {
      $ret[] = $row;
    }

    return $ret;
  }

  /**
   * Log HTTP_REFERERs on a given node when in full page mode.
   *
   * @param object $node
   *   The node to which referrer linked
   */
  function log_referrers($node) {
    global $base_url;
    $us_referrer = $_SERVER['HTTP_REFERER'];

    // Is us_referer local ? MUST use ===, otherwise FALSE would match too.
    if (!empty($us_referrer) && strpos($us_referrer, $base_url . '/') === 0) {
      // Extract local path, possibly aliased.
      $us_referrer = drupal_substr($us_referrer, drupal_strlen($base_url) + 1);

      // Unalias it.
      $us_referrer = drupal_get_normal_path($us_referrer);

      // Sanitize it.
      $referrer = check_plain($us_referrer);

      // XXX costly! Use a queue to batch such logging ?
      $q = db_merge('g2_referer')
        ->key(array(
          'nid' => $node->nid,
          'referer' => $referrer,
        ))
        ->fields(array(
          'nid' => $node->nid,
          'referer' => $referrer,
          'incoming' => 1,
        ))
        ->expression('incoming', 'incoming + :inc', array(':inc' => 1))
        ->execute();
    }
    else {
      /*
       * Referer is non-local.
       * Maybe we'll do something some day, but not right now
       */
    }
  }

  /**
   * Modify the default page title as built by Drupal.
   *
   * Tweaking $conf only modifies the live copy used by Drupal, and does not
   * touch the cached and stored values as would be the case using
   * variable_set().
   * */
  function override_site_name() {
    $default_page_title = t('G2 Glossary on @title');
    if (variable_get(VARPAGETITLE, $default_page_title)) {
      global $conf;
      $conf['site_name'] = strtr(variable_get(VARPAGETITLE, $default_page_title),
        array('@title' => isset($conf['site_name']) ? $conf['site_name'] : ''));
    }
  }

  /**
   * Page callback for G2\PATHENTRIES/%g2_entry
   *
   * TODO convert to render array format.
   *
   * @param array $nodes
   *   An array of nodes to render.
   *
   * @return string
   *   The rendered version.
   */
  function page_entries($nodes) {
    // Activate context.
    if ($plugin = context_get_plugin('condition', 'g2')) {
      $plugin->execute('g2_user');
    }

    $ret = theme('g2_entries', array('entries' => $nodes));
    return $ret;
  }

  /**
   * Page callback for G2 main page.
   *
   * Build the main G2 page, using an unpublished node and the alphabar.
   *
   * Return a simple nodepage built with alphabars wrapping a node
   * to serve as the main page for the G2 glossary. If no node
   * is set, just return an alphabar.
   *
   * XXX Port the D5 feature allowing a function name instead of a node.
   * XXX Find a better way to build that page
   *
   * The node is supposed to be an unpublished node, to avoid its
   * appearing in normal situations, and it will be used as if it was
   * published by this function.
   *
   * @return array
   *   Render array for page.
   */
  function page_main() {
    // Activate context.
    if ($plugin = context_get_plugin('condition', 'g2')) {
      $plugin->execute('g2_user');
    }

    // Rowlen == 2 << 16 so that only an extremely long alphabar would wrap.
    $alphabar = theme('g2_alphabar', array(
      'alphabar' => alphabar(),
      'rowlen' => 2 << 16,
    ));

    $generator = variable_get(VARMAIN, DEFMAIN);
    if (is_numeric($generator)) {
      $node = node_load($generator);
      if (is_object($node)) {
        drupal_set_title($node->title);
        override_site_name();
        if (!empty($node->body)) {
          // Simulate publishing.
          $node->status = NODE_PUBLISHED;
          $node->title = NULL;
          // Coder false positive: http://drupal.org/node/224333#node_view.
          $text = theme('node', node_view($node));
        }
        else {
          // Empty or missing body field.
          $text = '';
        }
      }
      else {
        // Node not found.
        $text = '';
      }
    }
    elseif (is_string($generator) && function_exists($generator)) {
      $text = $generator();
    }
    else {
      $text = '';
    }

    $ret = array(
      '#theme'    => 'g2_main',
      '#alphabar' => $alphabar,
      '#text'     => $text,
    );
    // $ret .= 'secu';
    return $ret;
  }

  /**
   * Ancillary function for g2_block to return a pseudo-random entry.
   *
   * Entry is selected to be different from the current WOTD and, in the default
   * setting, from the latest pseudo-random result returned.
   *
   * Only works for glossaries with 3 entries or more.
   *
   * XXX Likely possible to do better (2 or more) using a $rand, 2 range and
   *   removing the wotd from there.
   * XXX Likely possible to do better (2 or more) using an ORDER BY RAND().
   *
   * @return object
   *   Title / nid / teaser. Unfiltered contents.
   */
  function random() {
    $wotd_nid = variable_get(VARWOTDENTRY, DEFWOTDENTRY);

    // Do we have a stored previous random to exclude ?
    $random = variable_get(VARRANDOMSTORE, DEFRANDOMSTORE)
      ? variable_get(VARRANDOMENTRY, DEFRANDOMENTRY)
      // We don't, so just avoid untitled nodes, which should not exist anyway.
      : '';

    $q = db_select('node', 'n');
    $q->addExpression('COUNT(*)', 'cnt');
    $q->condition('n.type', NODETYPE)
      ->condition('n.status', 1)
      ->condition('n.title', $random, '!=')
      ->condition('n.nid', $wotd_nid, '!=')
      ->addTag('node_access');

    $result = $q->execute();
    $count = $result->fetchField();
    // No more need to mt_srand() since PHP 4.2.
    $rand = mt_rand(0, $count - 1);

    // Select from the exact same list of nodes, assuming none was inserted or
    // deleted in the meantime.
    $q = db_select('node', 'n');
    $q->innerJoin('node_revision', 'v', 'n.vid = v.vid');
    $q->fields('n', array('nid'))
      ->condition('n.type', NODETYPE)
      ->condition('n.status', 1)
      ->condition('n.title', $random, '!=')
      ->condition('n.nid', $wotd_nid, '!=')
      ->addTag('node_access')
      ->range($rand, 1);

    $result = $q->execute();
    $nid = $result->fetchField();
    if (!$nid) {
      $nid = $wotd_nid;
    }

    $node = node_load($nid);
    /*
    $node->taxonomy = (variable_get(VARRANDOMTERMS, DEFRANDOMTERMS) &&
      isset($node->taxonomy))
      ? $node->taxonomy
      : array();
    */

    if (variable_get(VARRANDOMSTORE, DEFRANDOMSTORE)) {
      // Unfiltered.
      variable_set(VARRANDOMENTRY, $node->title);
    }

    return $node;
  }

  /**
   * Counts the number of G2 referer entries.
   *
   * TODO: check referer wipe: it may have been damaged in the D6, then D7 ports
   *
   * @param array $form
   *   The form array.
   * @param array $form_state
   *   The current form state.
   * @param object $node
   *   The node for which references are to be counted.
   *
   * @return string
   *   HTML
   */
  function referer_links($form, $form_state, $node) {
    // Activate context.
    if ($plugin = context_get_plugin('condition', 'g2')) {
      $plugin->execute('g2_admin');
    }

    // Build list of referers.
    $nid = $node->nid;

    $header = array(
      array(
        'data' => t('Clicks'),
        'field' => 'incoming',
        'sort' => 'desc',
      ),
      array(
        'data' => t('Referer'),
        'field' => 'referer',
      ),
      array('data' => t('Related node')),
    );

    // Can be generated for unpublished nodes by author or admin, so don't
    // filter on node.status = 1
    // The join is needed to avoid showing info about forbidden nodes, and
    // to allow some modules to interfere without breaking because they
    // assume "nid" only exists in {node}.
    $q = db_select('g2_referer', 'gr')
      ->extend('TableSort');
    $q->innerJoin('node', 'n', 'gr.nid = n.nid');
    $q->fields('gr', array('referer', 'incoming'))
      ->condition('gr.nid', $nid)
      ->addTag('node_access')
      // TableSort.
      ->orderByHeader($header);

    // dsm((string) $q);
    $result = $q->execute();

    $rows = array();
    $matches = array();
    foreach ($result as $row) {
      $sts = preg_match('/node\/(\d+)/', $row->referer, $matches);
      if ($sts) {
        $node = node_load($matches[1]);
        $uri = entity_uri('node', $node);
        $title_link = l($node->title, $uri['path'], $uri['options']);
      }
      else {
        $title_link = NULL;
      }
      // Empty referer does not happen in normal situations, only with bugs.
      $rows[] = empty($row->referer)
        ? array($row->incoming, t('<empty>'), $title_link)
        : array($row->incoming, l($row->referer, $row->referer, array('absolute' => TRUE)), $title_link);
    }
    $message = empty($rows)
      ? t('No referer found. Maybe you just cleaned the list ?')
      : theme('table', array('header' => $header, 'rows' => $rows));

    // Build form from results.
    $form = array();
    $form['links'] = array(
      '#type' => 'markup',
      '#prefix' => t('<h3>Local referers for this node</h3>'),
      '#markup' => $message,
    );

    if (!empty($rows)) {
      $form['links']['#suffix'] = t("<p>WARNING: just because a click came from a node doesn't mean the node has a link.
        The click may have come from a block on the page. These stats are just a hint for editors.</p>");

      $form['wipe_target'] = array(
        '#type' => 'value',
        '#value' => $nid,
      );
      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Wipe referer info for this entry'),
      );
    }

    return $form;
  }

  /**
   * Submit handler for G2\referer_links().
   *
   * Use the wipe target to redirect to the wipe confirmation form. If we
   * hadn't been using a button for this link, we could just as well have
   * used a plain link.
   *
   * @param array $form
   *   The form array.
   * @param array $form_state
   *   The current form state.
   */
  function referer_links_submit(&$form, &$form_state) {
    $form_state['redirect'] = 'g2/wipe/' . $form_state['values']['wipe_target'];
  }

  /**
   * Erase the referer counts on g2 entries
   *
   * @param int $nid
   *   Node from which to erase referers, or NULL to erase all g2 referers
   */
  function referer_wipe($nid = NULL) {
    if (isset($nid)) {
      $deleted = db_delete('g2_referer')
        ->condition('nid', $nid)
        ->execute();
      $node = node_load($nid);
      $uri = entity_uri('node', $node);
      $ret = $uri;
    }
    else {
      $deleted = db_delete('g2_referer')
        ->execute();
      $ret = array('path' => PATHSETTINGS);
    }
    $message = $deleted
      ? format_plural($deleted, 'Deleted 1 G2 referer link', 'Deleted @count G2 referer links', array('@count' => $deleted))
      : t('No G2 referer link found for deletion.');
    // TODO Check_plain() is actually useless here. Counter-productive even ?
    drupal_set_message(check_plain($message));
    return $ret;
  }

  /**
   * Submit handler for "wipe referers" button on settings form.
   *
   * @param array $form
   *   The form array
   * @param array $form_state
   *   The current form state.
   */
  function referer_wipe_button_submit($form, &$form_state) {
    drupal_goto('g2/wipe');
  }

  /**
   * Form builder for the referer wipe confirmation request form.
   *
   * This is the same form for both global wipe and individual node wipe.
   *
   * @param array $form
   *   The form array
   * @param array $form_state
   *   The current form state.
   * @param object $node
   *   The node for which the form is built.
   *
   * @return array
   *   The confirmation form array.
   */
  function referer_wipe_confirm_form($form, &$form_state, $node = NULL) {
    // Activate context.
    if ($plugin = context_get_plugin('condition', 'g2')) {
      $plugin->execute('g2_admin');
    }

    if (is_object($node) && isset($node->nid)) {
      $question = t('Erase referer information from %title ?', array(
        '%title' => $node->title,
      ));
      // No entity_uri: this URL belongs to this module, not to the entity.
      $cancel = 'node/' . $node->nid . '/referers';
      $form['node'] = array(
        '#type'  => 'value',
        '#value' => $node,
      );
    }
    else {
      $question = t('Are you sure to want to erase the referer information on all G2 entries ?');
      $cancel = PATHSETTINGS;
    }

    $ret = confirm_form($form,
      $question,
      $cancel,
      t('This action cannot be undone.'),
      t('Confirm'),
      t('Cancel')
    );

    return $ret;
  }

  /**
   * Submit handler for referer wipe confirmation.
   */
  function referer_wipe_confirm_form_submit($form, &$form_state) {
    if (isset($form_state['values']['node'])) {
      $node = $form_state['values']['node'];
      $dest = referer_wipe($node->nid);
      drupal_set_message(t('Referer information has been erased from %title', array(
        '%title' => $node->title,
      )));
    }
    else {
      $dest = referer_wipe();
      drupal_set_message(t('Referer information has been erased from all G2 entries'));
    }
    // $dest['options'] ignored.
    $form_state['redirect'] = $dest['path'];
    return;
  }

  /**
   * Extract statistics from the G2 glossary.
   *
   * @param int $tid
   *   Taxonomy term id
   * @param string $initial
   *   Initial segment
   * @param boolean $fields
   *   Return by status (FALSE) or by field (TRUE)
   *
   * @return array
   *   - count of g2 entries having chosen taxonomy term
   *   - count of g2 entries starting with chosen initial segment
   *   Either sorted by node.status or by field name.
   */
  function stats($tid = 0, $initial = NULL, $fields = FALSE) {

    if ($fields) {
      // Count entries matching given tid only if one is given. The same tid:
      // - can be present for a given node in instances of more than one field
      // - can not be present on multiple instances of one field
      // ...so any count summing should also be deduplicated on a by-node basis.
      $fields = field_info_fields();
      $instances = field_info_instances('node', NODETYPE);
      if (!empty($tid)) {
        $field_type = 'taxonomy_term_reference';
        $ret = array();
        foreach ($fields as $field_name => $field) {
          if (empty($field['type']) || $field['type'] != $field_type) {
            continue;
          }

          $q = new EntityFieldQuery();
          $q->fieldCondition($field, 'tid', $tid)
            ->entityCondition('bundle', NODETYPE)
            ->age(FIELD_LOAD_CURRENT)
            ->count();
          $ret[$field_name] = array_merge($instances[$field_name], array('g2_node_count' => $q->execute()));
        }
      }
    }
    else {
      // XXX 20110118 also support taxonomy_maintain_index_table == TRUE
      /*
      1. if $tid, same EFQ without count()
         and propertyCondition('n.title', $initial . '%', 'LIKE')
         and propertyCondition('n.status', 0|1), so it runs twice. Silly.
      2. or just use taxonomy_index
      */

      $q = db_select('node', 'n');
      $q->addExpression('COUNT(DISTINCT n.nid)', 'cnt');
      $q->fields('n', array('status'))
        ->condition('n.type', NODETYPE)
        ->groupBy('n.status')
        ->addTag('node_access');

      if (isset($tid) && is_int($tid) && $tid > 0) {
        // TODO: migrate from taxonomy_index to field storage as per
        // taxonomy_update_7005().
        $q->innerJoin('taxonomy_index', 'ti', 'n.nid = ti.nid');
        $q->condition('ti.tid', $tid);
      }

      if (isset($initial) && !empty($initial)) {
        $q->condition('n.title', $initial . '%', 'LIKE');
      }
      // Avoid empty returns.
      $ret = array(
        NODE_NOT_PUBLISHED => 0,
        NODE_PUBLISHED => 0,
      );

      $result = $q->execute();
      foreach ($result as $row) {
        $ret[$row->status] = (int) $row->cnt;
      }
    }

    return $ret;
  }

  /**
   * Encodes terminal path portions for G2. This allows linking to things
   * containing #, + or '.', like C++, C# or the . initial.
   *
   * Warning: this is NOT a generic replacement for urlencode, but covers a very
   * specific glossary-related need.
   *
   * @param string $terminal
   *   The termin path portion to encode.
   *
   * @return string
   *   The encoded version.
   */
  function terminal_encode($terminal) {
    $terminal = strtr($terminal, array(
      '.' => '%2E',
      '/' => '%2F',
      '#' => '%23',
      '&' => '%26',
      '+' => '%2B',
    ));
    return $terminal;
  }

  /**
   * Returns a list of the top n nodes as counted by statistics.module.
   *
   * - Unpublished nodes are not listed.
   * - Stickyness is ignored for ordering, but returned in the results for
   *   client-side ordering if needed.
   *
   * @param int $max
   *   Number or entries to return.
   * @param boolean $daily_top
   *   Order by daily views if TRUE, otherwise by total views (default).
   *
   * @return array|NULL
   *   Statistics will be empty without statistics module.
   *   Note that the title of the nodes is NOT filtered.
   */
  function top($max = NULL, $daily_top = FALSE, $include_unpublished = FALSE) {
    $def_max      = variable_get(VARTOPITEMCOUNT, DEFTOPITEMCOUNT);
    $rpc_throttle = variable_get(VARRPCTHROTTLE,  DEFRPCTHROTTLE);

    // Limit extraction.
    if (empty($max) or ($max > $rpc_throttle * $def_max)) {
      $max = $def_max;
    }

    // Only list unpublished nodes if requested and allowed.
    $status = ($include_unpublished && user_access('administer nodes'))
      ? NODE_NOT_PUBLISHED
      : NODE_PUBLISHED;

    $ret = array();
    if (function_exists('statistics_node_view')) {
      $q = db_select('node', 'n');
      $q->innerJoin('node_counter', 'nc', 'n.nid = nc.nid');
      $q->fields('n', array('nid', 'title', 'status', 'sticky', 'type'))
        ->fields('nc', array('daycount', 'totalcount'))
        ->condition('n.type', NODETYPE)
        ->condition('n.status', $status, '>=')
        ->isNotNull('nc.totalcount');
      if ($daily_top) {
        $q->orderBy('nc.daycount', 'DESC');
      }
      else {
        $q->orderBy('nc.totalcount', 'DESC');
      }
      $q->orderBy('n.changed', 'DESC')
        ->addTag('node_access')
        ->range(0, $max);

      $result = $q->execute();
      foreach ($result as $row) {
        $ret[(int) $row->nid] = $row;
      }
    }

    return $ret;
  }

  /**
   * Returns a structure for the WOTD.
   *
   * TODO 20110122 replace by a node with a specific build mode
   *
   * @param int $bodysize
   *   The maximum length of the body to use for the WOTD.
   *
   * @return object
   *   title / nid / teaser.
   *   Teaser and Body are returned already filtered, not stripped.
   */
  function wotd($bodysize = 0) {
    // No need for a static: this function is normally never called twice.
    $nid = variable_get(VARWOTDENTRY, DEFWOTDENTRY);
    $node = node_load($nid);
    if (empty($node)) {
      return NULL;
    }

    if (variable_get(VARWOTDTERMS, DEFWOTDTERMS)) {
      $node->taxonomy = taxonomy_node_get_terms($node);
    }

    if (!empty($node->teaser)) {
      $node->teaser = check_markup($node->teaser, $node->format);
    }

    $node->truncated = FALSE;

    // Use a node view instead.
    if (FALSE && $bodysize > 0 && !empty($node->body)) {
      // Save the raw version.
      $node->raw_body = $node->body;
      if (drupal_strlen($node->body) > $bodysize) {
        $node->truncated = TRUE;
        $body = drupal_substr($node->body, 0, $bodysize);
        $node->body =  check_markup($body, $node->format);
      }
    }

    return $node;
  }

  /**
   * Generate an RSS feed containing the latest WOTD.
   *
   * TODO check the Language to use in D7
   *
   * @return string
   *   XML in UTF-8 encoding
   */
  function wotd_feed() {
    global $base_url;

    $channelinfo = array(
      // Link element:  Drupal 4.7->6 defaults to $base url
      // Language: Drupal 6 defaults to to $language->language
      // Title: Drupal 6 defaults to site name.
      'title' => variable_get(VARWOTDFEEDTITLE, variable_get(VARWOTDTITLE, t('Word of the day in the G2 glossary'))),
      // Description: Drupal defaults to $site_mission
      'description' => strtr(variable_get(VARWOTDFEEDDESCR, t('A daily definition from the G2 Glossary at !site')), array('!site' => $base_url)),
      'managingEditor' => variable_get('site_mail', 'nobody@example.com'),
    );

    $items = array(variable_get(VARWOTDENTRY, DEFWOTDENTRY));
    // Adds the XML content-type + encoding header.
    $ret = node_feed($items, $channelinfo);

    $ret = check_plain($ret);
    echo $ret;
    exit();
  }
}
