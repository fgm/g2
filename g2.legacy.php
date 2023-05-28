<?php

/**
 * @file
 * This is unported code for the module.
 */

use Drupal\g2\G2;

/**
 * Implements hook_block_configure().
 */
function zg2_block_configure($delta) {
  $count_options = [
    '1' => '1',
    '2' => '2',
    '5' => '5',
    '10' => '10',
  ];
  $info = g2_block_info();
  $info = $info[$delta];
  $form['caching'] = [
    '#markup' => t('<p>Caching mode: @mode</p>',
      ['@mode' => G2::block_cache_decode($info['cache'])]),
  ];

  switch ($delta) {
    case G2::DELTA_RANDOM:
      $form[G2::VARRANDOMSTORE] = [
        '#type' => 'checkbox',
        '#title' => t('Store latest random entry'),
        '#default_value' => variable_get(G2::VARRANDOMSTORE,
          G2::DEFRANDOMSTORE),
        '#description' => t(
          'When this setting is TRUE (default value),
      the latest random value is kept in the DB to avoid showing the same pseudo-random
      value on consecutive page displays.
      For small sites, it is usually best to keep it saved.
      For larger sites, unchecking this setting will remove one database write with locking.'
        ),
      ];
      $form[G2::VARRANDOMTERMS] = [
        '#type' => 'checkbox',
        '#title' => t('Return taxonomy terms for the current entry'),
        '#default_value' => variable_get(G2::VARRANDOMTERMS,
          G2::DEFRANDOMTERMS),
        '#description' => t(
          'The taxonomy terms will be returned by the API and made available to the theme.
         Default G2 themeing will display them.'
        ),
      ];
      break;

    case G2::DELTA_TOP:
      $form[G2::VARTOPITEMCOUNT] = [
        '#type' => 'select',
        '#title' => t('Number of items'),
        '#default_value' => variable_get(G2::VARTOPITEMCOUNT,
          G2::DEFTOPITEMCOUNT),
        '#options' => $count_options,
      ];
      break;

    case G2::DELTA_WOTD:
      // Convert nid to "title [<nid>]" even if missing.
      // @see autocomplete()
      $nid = variable_get(G2::VARWOTDENTRY, G2::DEFWOTDENTRY);
      $node = \Drupal::service('entity_type.manager')
        ->getStorage('node')
        ->load($nid);
      if (empty($node)) {
        $node = new stdClass();
        $node->nid = 0;
        $node->title = NULL;
      }
      $form[G2::VARWOTDENTRY] = [
        '#type' => 'textfield',
        '#title' => t('Entry for the day'),
        '#maxlength' => 60,
        '#autocomplete_path' => G2::PATH_AUTOCOMPLETE,
        '#required' => TRUE,
        // !title: we don't filter since this is input, not output,
        // and can contain normally escaped characters, to accommodate
        // entries like "<", "C#" or "AT&T"
        '#default_value' => t(
          '!title [@nid]',
          [
            '!title' => $node->title,
            '@nid' => $nid,
          ]
        ),
      ];
      $form[G2::VARWOTDBODYSIZE] = [
        '#type' => 'textfield',
        '#title' => t('Number of text characters to be displayed from entry definition body, if one exists'),
        '#size' => 4,
        '#maxlength' => 4,
        '#required' => TRUE,
        '#default_value' => variable_get(G2::VARWOTDBODYSIZE,
          G2::DEFWOTDBODYSIZE),
      ];
      $form[G2::VARWOTDAUTOCHANGE] = [
        '#type' => 'checkbox',
        '#title' => t('Auto-change daily'),
        '#required' => TRUE,
        '#default_value' => variable_get(G2::VARWOTDAUTOCHANGE,
          G2::DEFWOTDAUTOCHANGE),
        '#description' => t('This setting will only work if cron or poormanscron is used.'),
      ];
      $form[G2::VARWOTDTERMS] = [
        '#type' => 'checkbox',
        '#title' => t('Return taxonomy terms for the current entry'),
        '#default_value' => variable_get(G2::VARWOTDTERMS, G2::DEFWOTDTERMS),
        '#description' => t(
          'The taxonomy terms will be returned by the API and made available to the theme.
         Default G2 themeing will display them.'
        ),
      ];
      $default_wotd_title = t('Word of the day in the G2 glossary');
      $form[G2::VARWOTDTITLE] = [
        '#type' => 'textfield',
        '#title' => t('Title for the WOTD block'),
        '#description' => t(
          'This title is also the default title for the WOTD feed, if none is defined. It is overridden by the default Drupal block title, if the latter is not empty.'
        ),
        '#required' => TRUE,
        '#default_value' => variable_get(G2::VARWOTDTITLE, $default_wotd_title),
      ];

      $form['wotd_feed'] = [
        '#type' => 'fieldset',
        '#title' => t('RSS Feed'),
      ];
      $form['wotd_feed'][G2::VARWOTDFEEDLINK] = [
        '#type' => 'checkbox',
        '#title' => t('Display feed link'),
        '#default_value' => variable_get(G2::VARWOTDFEEDLINK,
          G2::DEFWOTDFEEDLINK),
        '#description' => t('Should the theme display the link to the RSS feed for this block ?'),
      ];
      $form['wotd_feed'][G2::VARWOTDFEEDTITLE] = [
        '#type' => 'textfield',
        '#title' => t('The feed title'),
        '#size' => 60,
        '#maxlength' => 60,
        '#required' => TRUE,
        '#default_value' => variable_get(G2::VARWOTDFEEDTITLE,
          variable_get(G2::VARWOTDTITLE, $default_wotd_title)),
        '#description' => t(
          'The title for the feed itself.
         This will typically be used by aggregators to remind users of the feed and link to it.
         If nulled, G2 will reset it to the title of the block.'
        ),
      ];
      $form['wotd_feed'][G2::VARWOTDFEEDAUTHOR] = [
        '#type' => 'textfield',
        '#title' => t('The feed item author'),
        '#size' => 60,
        '#maxlength' => 60,
        '#required' => TRUE,
        '#default_value' => variable_get(G2::VARWOTDFEEDAUTHOR,
          G2::DEFWOTDFEEDAUTHOR),
        '#description' => t(
          'The author name to be included in the feed entries.
      In this string @author will be replaced by the actual author information.'
        ),
      ];
      $form['wotd_feed'][G2::VARWOTDFEEDDESCR] = [
        '#type' => 'textfield',
        '#title' => t('The feed description'),
        '#size' => 60,
        '#maxlength' => 60,
        '#required' => TRUE,
        '#default_value' => variable_get(G2::VARWOTDFEEDDESCR,
          t('A daily definition from the G2 Glossary at !site')),
        '#description' => t(
          'The description for the feed itself.
      This will typically be used by aggregators when describing the feed prior to subscription.
      It may contain !site, which will dynamically be replaced by the site base URL.'
        ),
      ];
      break;

    default:
      break;
  }
  return $form;
}

/**
 * Implements hook_block_info().
 */
function zg2_block_info() {
  $blocks = [];
  $blocks[G2::DELTA_RANDOM]['info'] = variable_get('g2_random_info',
    t('G2 Random'));
  $blocks[G2::DELTA_TOP]['info'] = variable_get('g2_top_info', t('G2 Top'));
  $blocks[G2::DELTA_WOTD]['info'] = variable_get('g2_wotd_info',
    t('G2 Word of the day'));

  // Else it couldn't be random.
  $blocks[G2::DELTA_RANDOM]['cache'] = DRUPAL_NO_CACHE;
  // Can contain unpublished nodes.
  $blocks[G2::DELTA_TOP]['cache'] = DRUPAL_CACHE_PER_ROLE;
  // Not all roles have g2 view permission.
  $blocks[G2::DELTA_WOTD]['cache'] = DRUPAL_CACHE_PER_ROLE;
  return $blocks;
}

/**
 * Implements hook_block_save().
 */
function zg2_block_save($delta, $edit) {
  switch ($delta) {
    case G2::DELTA_RANDOM:
      variable_set(G2::VARRANDOMSTORE, $edit[G2::VARRANDOMSTORE]);
      variable_set(G2::VARRANDOMTERMS, $edit[G2::VARRANDOMTERMS]);
      break;

    case G2::DELTA_TOP:
      variable_set(G2::VARTOPITEMCOUNT, $edit[G2::VARTOPITEMCOUNT]);
      break;

    case G2::DELTA_WOTD:
      // Convert "some title [<nid>, sticky]" to nid.
      $entry = $edit[G2::VARWOTDENTRY];
      $matches = [];
      $count = preg_match('/.*\[(\d*).*\]$/', $entry, $matches);
      $nid = $count ? $matches[1] : 0;

      variable_set(G2::VARWOTDENTRY, $nid);
      variable_set(G2::VARWOTDBODYSIZE, $edit[G2::VARWOTDBODYSIZE]);
      variable_set(G2::VARWOTDAUTOCHANGE, $edit[G2::VARWOTDAUTOCHANGE]);
      variable_set(G2::VARWOTDDATE, \Drupal::time()->getRequestTime());
      variable_set(G2::VARWOTDTERMS, $edit[G2::VARWOTDTERMS]);
      variable_set(G2::VARWOTDFEEDLINK, $edit[G2::VARWOTDFEEDLINK]);
      variable_set(G2::VARWOTDFEEDTITLE, $edit[G2::VARWOTDFEEDTITLE]);
      variable_set(G2::VARWOTDFEEDDESCR, $edit[G2::VARWOTDFEEDDESCR]);
      variable_set(G2::VARWOTDFEEDAUTHOR, $edit[G2::VARWOTDFEEDAUTHOR]);
      variable_set(G2::VARWOTDTITLE, $edit[G2::VARWOTDTITLE]);
      break;

    default:
      break;
  }
}

/**
 * Implements hook_block_view().
 */
function zg2_block_view($delta) {
  switch ($delta) {
    case G2::DELTA_RANDOM:
      $block['subject'] = t('Random G2 glossary entry');
      $block['content'] = theme('g2_random', ['node' => G2::random()]);
      break;

    case G2::DELTA_TOP:
      $max = variable_get(G2::VARTOPITEMCOUNT, G2::DEFTOPITEMCOUNT);
      $block['subject'] = t(
        '@count most popular G2 glossary entries',
        ['@count' => $max]
      );
      $block['content'] = theme('g2_node_list',
        ['nodes' => G2::top($max, FALSE, TRUE)]);
      break;

    case G2::DELTA_WOTD:
      $block['subject'] = variable_get(G2::VARWOTDTITLE,
        t('Word of the day in the G2 glossary'));
      $block['content'] = theme('g2_wotd', [
        'node' => G2::wotd(variable_get(G2::VARWOTDBODYSIZE,
          G2::DEFWOTDBODYSIZE)),
      ]);
      break;

    // Should happen only when using a new code version on an older schema
    // without updating: ignore.
    default:
      $block = NULL;
      break;
  }

  return $block;
}

/**
 * Implements hook_context_plugins().
 *
 * This is a ctools plugins hook.
 */
function zg2_context_plugins() {
  Drupal::moduleHandler()->loadInclude('g2', 'inc', 'context/g2.plugins');
  return _g2_context_plugins();
}

/**
 * Implements hook_context_registry().
 */
function zg2_context_registry() {
  Drupal::moduleHandler()->loadInclude('g2', 'inc', 'context/g2.plugins');
  return _g2_context_registry();
}

/**
 * Implements hook_cron().
 *
 * In G2's case, change the WOTD once a day if this feature is enabled,
 * which is the default case.
 */
function zg2_cron() {
  if (variable_get(G2::VARWOTDAUTOCHANGE, G2::DEFWOTDAUTOCHANGE)) {
    $date0 = date('z',
      variable_get(G2::VARWOTDDATE, \Drupal::time()->getRequestTime()));
    $date1 = date('z');
    if ($date1 <> $date0) {
      $random = G2::random();
      variable_set(G2::VARWOTDENTRY, $random->nid);
      variable_set(G2::VARWOTDDATE, mktime());
    }
  }
}

/**
 * Implements hook_ctools_plugin_api().
 */
function zg2_ctools_plugin_api($module, $api) {
  if ($module == 'context' && $api == 'context') {
    $ret = [
      'version' => 3,
      'path' => ExtensionPathResolver::getPath('module', 'g2') . '/context',
      // Not until http://drupal.org/node/1242632 is fixed
      // 'file' => 'g2.context_defaults.inc'.
    ];
  }
  else {
    $ret = NULL;
  }

  return $ret;
}

/**
 * Implements hook_delete().
 */
function zg2_delete($node) {
  \Drupal::database()->delete('g2_node')
    ->condition('nid', $node->nid)
    ->execute();
}

/**
 * Implements hook_field_extra_fields().
 */
function zg2_field_extra_fields() {
  $expansion = [
    'label' => t('Expansion'),
    'description' => t('For acronyms/initialisms, this is the expansion of the initials to full words'),
    'weight' => 0,
  ];
  $period = [
    'label' => t('Life period'),
    'description' => t(
      'This is the period of time during which the entity described by the term was actually alive, not the lifetime of the term itself, since any term is immortal to some extent.'
    ),
    'weight' => 1,
  ];
  $extra_title = [
    'label' => 'Extra title',
    'description' => t('The optional CSS-hidden extra title on node displays'),
    'weight' => 99,
  ];

  $extra['node'][G2::NODE_TYPE] = [
    'form' => [
      'expansion' => $expansion,
      'period' => $period,
      'complement' => [
        'label' => t('Complement'),
        'description' => t('Additional non-versioned editor-only meta-information about the definition'),
        'weight' => 2,
      ],
      'origin' => [
        'label' => t('IP/Origin'),
        'description' => t(
          'Additional non-versioned editor-only Intellectual Property/Origin information about the definition'
        ),
        'weight' => 3,
      ],
    ],
    'display' => [
      'expansion' => $expansion,
      'period' => $period,
      'extra_title' => $extra_title,
    ],
  ];

  return $extra;
}

/**
 * Implements hook_filter_info().
 */
function zg2_filter_info() {
  $filters = [
    'filter_g2' => [
      'title' => t('G2 Glossary filter'),
      'description' => t('Allows users to link to G2 entries using &lt;dfn&gt; elements.'),
      'prepare callback' => 'G2::filter_prepare',
      'process callback' => 'G2::filter_process',
      'tips callback' => 'G2::filter_tips',
    ],
  ];

  return $filters;
}

/**
 * Implements hook_form().
 *
 * XXX 20110122 use fields, not properties for expansion/period/editor info.
 */
function zg2_form(&$node, $form_state) {
  $admin = user_access('bypass node access')
    || user_access('edit any g2_entry content')
    || (user_access('edit own g2_entry content') && $user->uid == $node->uid);

  $type = node_type_get_type($node);

  // Pre-fill title information on URL-based node creation.
  if (!isset($node->title)) {
    $node->title = check_plain(
      drupal_substr(
        \Drupal::requestStack()->getCurrentRequest()->query->get('q'),
        drupal_strlen(G2::PATH_NODE_ADD) + 1
      )
    );
  }

  $form = [];

  $form['content'] = [
    '#type' => 'fieldset',
    '#title' => t('Contents'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#weight' => -10,
  ];
  $form['content']['title'] = [
    '#type' => 'textfield',
    '#title' => check_plain($type->title_label),
    '#required' => TRUE,
    '#default_value' => $node->title,
    '#weight' => -5,
    '#description' => t('Plain text: no markup allowed.'),
  ];

  $form['content']['expansion'] = [
    '#type' => 'textfield',
    '#title' => t('Entry expansion (for acronyms/initialisms)'),
    '#required' => FALSE,
    '#default_value' => $node->expansion ?? NULL,
    '#description' => t('Plain text: no markup allowed.'),
  ];

  $form['content']['period'] = [
    '#type' => 'textfield',
    '#title' => t('Life period of this entry'),
    '#required' => FALSE,
    '#description' => t(
      'This is the period of time during which the entity described by the term was actually alive, not the lifetime of the term itself, since any term is immortal to some extent. Plain text, no markup allowed.'
    ),
    '#default_value' => $node->period ?? NULL,
  ];

  // Hide published-only secondary information in a vertical tab.
  $form['publishing'] = [
    '#type' => 'fieldset',
    '#title' => t('Editor-only information'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#description' => t('Information in this box is not published in view mode, only during node edition.'),
    '#group' => 'additional_settings',
    '#weight' => -5,
    '#access' => $admin,
    '#attached' => [
      'js' => [ExtensionPathResolver::getPath('module', 'g2') . '/g2.js'],
    ],
  ];
  $form['publishing']['complement'] = [
    '#type' => 'textarea',
    '#title' => t('Complement'),
    '#rows' => 10,
    '#required' => FALSE,
    '#description' => t('Information not pertaining to origin of document: comments, notes...'),
    '#default_value' => $node->complement ?? NULL,
    '#access' => $admin,
  ];
  $form['publishing']['origin'] = [
    '#type' => 'textarea',
    '#title' => t('Origin/I.P.'),
    '#rows' => 10,
    '#required' => FALSE,
    '#description' => t('Informations about the origin/IP licensing of the definition'),
    '#default_value' => $node->origin ?? NULL,
    '#access' => $admin,
  ];

  return $form;
}

/**
 * Implements hook_insert().
 *
 * XXX New feature to add: make extra node info revision-aware.
 */
function zg2_insert($node) {
  drupal_write_record('g2_node', $node);
}

/**
 * Implements hook_load().
 *
 * Access control was performed earlier by core: no need to do it again here.
 *
 * XXX New feature to add: make extra node info revision-aware.
 */
function zg2_load($nodes) {
  $q = $q = \Drupal::database()->select('g2_node', 'gn');
  $result = $q->fields('gn')
    ->condition('gn.nid', array_keys($nodes), 'IN')
    ->execute();

  foreach ($result as $row) {
    foreach ($row as $property => $col) {
      $nodes[$row->nid]->$property = $col;
    }
  }
}

/**
 * Menu loader for g2_node.
 *
 * @param int $us_nid
 *   Safety with regard to $us_nid is checked within node_load().
 *
 * @return object|false|null
 *   - loaded object if accessible G2 node
 *   - NULL if accessible object is not a G2 node
 *   - FALSE otherwise
 */
function zg2_nid_load($us_nid = 0) {
  $node = \Drupal::service('entity_type.manager')
    ->getStorage('node')
    ->load($us_nid);
  if ($node->type != G2::NODE_TYPE) {
    $node = NULL;
  }
  return $node;
}

/**
 * Implements hook_node_access().
 */
function zg2_node_access($node, $op, $account) {
  switch ($op) {
    case 'create':
    case 'delete':
    case 'update':
      $ret = user_access(G2::PERM_ADMIN, $account);
      break;

    case 'view':
      $ret = user_access(G2::PERM_VIEW, $account);
      break;

    default:
      $uri = entity_uri('node', $node);
      watchdog(
        'g2',
        'Node access for invalid op %op',
        ['%op' => $op],
        WATCHDOG_NOTICE,
        l($node->title, $uri['path'], $uri['options'])
      );
      $ret = FALSE;
  }

  return $ret;
}

/**
 * Implements hook_node_info().
 */
function zg2_node_info() {
  $ret = [
    G2::NODE_TYPE => [
      'name' => t('G2 entry'),
      'base' => 'g2',
      'description' => t(
        'A G2 entry is a term (usual sense, not drupal sense) for which a definition and various additional information is provided, notably at the editorial level'
      ),
      'help' => t(
        'The title should be either a acronym/initialism or a normal word. If it is an acronym/initialism, use the expansion field to decode it, not the definition field.'
      ),
      'has_title' => TRUE,
      'title_label' => t('Term to define'),
    ],
  ];
  return $ret;
}

/**
 * Implements hook_node_view().
 *
 * Change the publication date only for the WOTD feed so that even old
 * terms, when chosen for publication, reflect the publication date,
 * instead of the node creation date as is the default.
 *
 * - Do not apply to non-G2 nodes.
 * - Do not apply to non-WOTD feeds.
 */
function zg2_node_view($node, $view_mode, $langcode) {
  $q = \Drupal::requestStack()->getCurrentRequest()->query->get('q');
  if ($view_mode == 'rss' && $node->type == G2::NODE_TYPE && ($q == G2::PATH_WOTD_FEED)) {
    $node->created = variable_get(G2::VARWOTDDATE,
      \Drupal::time()->getRequestTime());
    $node->name = filter_xss_admin(
      strtr(
        variable_get(G2::VARWOTDFEEDAUTHOR, '@author'),
        ['@author' => check_plain($node->name)]
      )
    );
  }
}

/**
 * Implements hook_permission().
 */
function zg2_permission() {
  $ret = [
    G2::PERM_ADMIN => [
      'title' => t('Administer G2 entries'),
      'description' => t(
        'Access administrative information on G2 entries. This permission does not grant access to the module settings, which are controlled by the "administer site configuration" permission.'
      ),
      'restrict access' => TRUE,
    ],
    G2::PERM_VIEW => [
      'title' => t('View G2 entries'),
      'description' => t('This permission allows viewing G2 entries, subject to additional node access control.'),
    ],
  ];
  return $ret;
}

/**
 * Implements hook_preprocess_page().
 *
 * - Introduce G2 page template suggestion when page is in a G2 context.
 */
function zg2_preprocess_page(&$vars) {
  if ($plugin = context_get_plugin('reaction', 'g2_template')) {
    $plugin->execute($vars);
  }
}

/**
 * Implements hook_update().
 */
function zg2_update($node) {
  drupal_write_record('g2_node', $node, 'nid');
}

/**
 * Implements hook_user_load().
 */
function zg2_user_load($users) {
  $q = \Drupal::database()->select('node', 'n');
  $result = $q->fields('n', ['nid', 'title', 'uid', 'type'])
    ->condition('n.type', G2::NODE_TYPE)
    ->condition('n.status', 1)
    ->condition('n.uid', array_keys($users), 'IN')
    ->orderBy('n.changed', 'DESC')
    ->orderBy('n.created', 'DESC')
    ->addTag('node_access')
    ->range(0, 10)
    ->execute();
  foreach ($result as $row) {
    $uri = entity_uri('node', $row);
    $uri['options']['absolute'] = TRUE;
    $users[$row->uid]->nodes[] = [
      'value' => l($row->title, $uri['path'], $uri['options']),
    ];
  }
}

/**
 * Implements hook_user_view().
 */
function zg2_user_view($account, $view_mode, $langcode) {
  if (isset($account->nodes) && count($account->nodes) >= 1) {
    $nodes = [];
    foreach ($account->nodes as $node) {
      $nodes[] = $node['value'];
    }
    $account->content['summary']['g2'] = [
      '#type' => 'user_profile_item',
      '#title' => t('Recent G2 definitions'),
      '#markup' => theme('item_list', ['items' => $nodes]),
    ];
  }
}

/**
 * Implements hook_view().
 */
function zg2_view($node, $view_mode) {
  $title = check_plain($node->title);

  if (node_is_page($node)) {
    $bc = drupal_get_breadcrumb();
    $bc[] = l(G2::TITLE_MAIN,
      $g2_home = variable_get(G2::VARPATHMAIN, G2::DEFPATHMAIN));
    $initial = drupal_substr($title, 0, 1);
    $bc[] = l($title[0], $g2_home . '/initial/' . $initial);
    unset($initial);
    drupal_set_breadcrumb($bc);
    G2::override_site_name();

    // Only log referrers on full page views.
    if (variable_get(G2::VARLOGREFERRERS, G2::DEFLOGREFERRERS)) {
      G2::log_referrers($node);
    }

    // Activate context.
    if ($plugin = context_get_plugin('condition', 'g2')) {
      $plugin->execute('g2_node');
    }
  }

  /*
  // Build more link, apply input format, including sanitizing.
  $node = node_prepare($node, $teaser);
   */

  if (!empty($node->expansion)) {
    $node->content['g2_expansion'] = [
      '#markup' => theme(
        'g2_field',
        [
          'name' => 'expansion',
          'title' => t('In other words'),
          'data' => $node->expansion,
        ]
      ),
    ];
  }

  if (!empty($node->period)) {
    $node->content['g2_period'] = [
      '#markup' => theme(
        'g2_field',
        [
          'name' => 'period',
          'title' => t('Term time period'),
          'data' => $node->period,
        ]
      ),
      '#weight' => 2,
    ];
  }

  // The following line adds invisible text that will be prepended to
  // the node in case some search routine favors the beginning of the
  // body. It can be turned off in case search engines frown upon this.
  if (variable_get(G2::VARHIDDENTITLE, G2::DEFHIDDENTITLE)) {
    $node->content['g2_extra_title'] = [
      '#markup' => '<div class="g2-extra-title">'
      . check_plain($node->title)
      . '</div>',
      '#weight' => -1,
    ];
  }

  return $node;
}

/**
 * Implements hook_view_api().
 */
function zg2_views_api() {
  return [
    'api' => '3.0',
    'path' => ExtensionPathResolver::getPath('module', 'g2') . '/views',
  ];
}

/**
 * Return a themed g2 node pseudo-field, like expansion or period.
 *
 * These are not filtered prior to invoking this theme function
 * within g2_view() (unlike D4.x->D6), so function performs filter_xss'ing.
 *
 * @param array $variables
 *   - Key g2-name: the name of the pseudo-field.
 *   - Key g2-title: the title for the pseudo-field.
 *   - Key g2-data: the contents of the pseudo-field.
 *
 * @return string
 *   HTML: the themed pseudo-field.
 */
function ztheme_g2_field($variables) {
  // Set in code, not by user, so assumed safe.
  $title = $variables['title'];

  $name = 'g2-' . $variables['name'];

  // Set by user, so unsafe.
  $data = filter_xss($variables['data']);

  $ret = <<<EOT
<div class="field field-name-body field-type-text-with-summary field-label-above $name">
<div class="field-label">$title:</div>
<div class="field-item even">
<p>$data</p>
</div><!-- field-item -->
</div><!-- field ... -->
EOT;

  return $ret;
}

/**
 * Theme a random entry.
 *
 * This is actually a short view for just about any single node, but it
 * is even shorter than node_view($node, TRUE).
 *
 * @return string
 *   HTML: the themed entry.
 *
 * @todo 20110122: replace with just a node rendered with a specific view_mode
 */
function ztheme_g2_random($variables) {
  $node = $variables['node'];
  $uri = entity_uri('node', $node);
  $ret = l($node->title, $uri['path'], $uri['options']);
  if (!empty($node->expansion)) {
    // Why t() ? Because varying languages have varying takes on spaces before /
    // after semicolons.
    $ret .= t(': @expansion', ['@expansion' => $node->expansion]);
  }
  // No longer hard coded: use a view_mode instead.
  // No need to test: also works on missing taxonomy
  // $ret .= G2::entry_terms($node);
  $ret .= theme(
    'more_link',
    [
      'url' => $uri['path'],
      // @todo Check evolution of http://drupal.org/node/1036190.
      'options' => $uri['options'],
      'title' => t('&nbsp;(+)'),
    ]
  );
  return $ret;
}

/**
 * Theme a WOTD block.
 *
 * @param object $variables
 *   The node for the word of the day. teaser and body are already filtered and
 *   truncated if needed.
 *
 * @return null|string
 *   title / nid / teaser / [body]
 *
 * @todo 20110122: replace with just a node rendered with a specific view_mode
 */
function ztheme_g2_wotd($variables) {
  $node = $variables['node'];
  if (empty($node)) {
    return NULL;
  }
  $uri = entity_uri('node', $node);

  $link = l($node->title, $uri['path'], $uri['options']);
  if (isset($node->expansion) and !empty($node->expansion)) {
    // Teaser already filtered by G2::wotd(), don't filter twice.
    // @todo 20110122 make sure this is true.
    $teaser = '<span id="g2_wotd_expansion">' . strip_tags($node->expansion) . '</span>';
    $ret = t(
      '!link: !teaser',
      [
        '!link' => $link,
        '!teaser' => $teaser,
      ]
    );
    unset($teaser);
  }
  else {
    $ret = $link;
  }

  // No longer needed: use a view_mode instead.
  /*
  if (!empty($node->body)) {
  // already filtered by G2::wotd(), don't filter twice, just strip.
  $body = strip_tags($node->body);
  if ($node->truncated) {
  $body .= '&hellip;';
  }
  $ret .= '<div id="g2_wotd_body">' . $body . '</div>';
  }
   */

  // No need to test: it won't change anything unless taxonomy has been returned
  // $ret .= G2::entry_terms($node);
  $ret .= theme(
    'more_link',
    [
      'url' => $uri['path'],
      // @todo Check evolution of http://drupal.org/node/1036190
      'options' => $uri['options'],
      'title' => t('&nbsp;(+)'),
    ]
  );
  if (variable_get(G2::VARWOTDFEEDLINK, G2::DEFWOTDFEEDLINK)) {
    $ret .= theme(
      'feed_icon',
      [
        'url' => url(G2::PATH_WOTD_FEED, ['absolute' => TRUE]),
        // @todo Find a better title.
        'title' => t('Glossary feed'),
      ]
    );
  }

  return $ret;
}
