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
    case G2::DELTA_TOP:
      $form[G2::VARTOPMAXCOUNT] = [
        '#type' => 'select',
        '#title' => t('Number of items'),
        '#default_value' => variable_get(G2::VARTOPMAXCOUNT,
          G2::DEFTOPMAXCOUNT),
        '#options' => $count_options,
      ];
      break;

    case G2::DELTA_WOTD:
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
  $blocks[G2::DELTA_TOP]['info'] = variable_get('g2_top_info', t('G2 Top'));

  // Can contain unpublished nodes.
  $blocks[G2::DELTA_TOP]['cache'] = DRUPAL_CACHE_PER_ROLE;
  return $blocks;
}

/**
 * Implements hook_block_save().
 */
function zg2_block_save($delta, $edit) {
  switch ($delta) {
    case G2::DELTA_TOP:
      variable_set(G2::VARTOPMAXCOUNT, $edit[G2::VARTOPMAXCOUNT]);
      break;

    case G2::DELTA_WOTD:
      variable_set(G2::VARWOTDFEEDLINK, $edit[G2::VARWOTDFEEDLINK]);
      variable_set(G2::VARWOTDFEEDTITLE, $edit[G2::VARWOTDFEEDTITLE]);
      variable_set(G2::VARWOTDFEEDDESCR, $edit[G2::VARWOTDFEEDDESCR]);
      variable_set(G2::VARWOTDFEEDAUTHOR, $edit[G2::VARWOTDFEEDAUTHOR]);
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
    case G2::DELTA_TOP:
      $max = variable_get(G2::VARTOPMAXCOUNT, G2::DEFTOPMAXCOUNT);
      $block['subject'] = t(
        '@count most popular G2 glossary entries',
        ['@count' => $max]
      );
      $block['content'] = theme('g2_node_list',
        ['nodes' => G2::top($max, FALSE, TRUE)]);
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

  $extra[G2::TYPE][G2::BUNDLE] = [
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
 * Implements hook_user_load().
 */
function zg2_user_load($users) {
  $q = \Drupal::database()->select(G2::TYPE, 'n');
  $result = $q->fields('n', ['nid', 'title', 'uid', 'type'])
    ->condition('n.type', G2::BUNDLE)
    ->condition('n.status', 1)
    ->condition('n.uid', array_keys($users), 'IN')
    ->orderBy('n.changed', 'DESC')
    ->orderBy('n.created', 'DESC')
    ->addTag('node_access')
    ->range(0, 10)
    ->execute();
  foreach ($result as $row) {
    $uri = entity_uri(G2::TYPE, $row);
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
  $node = $variables[G2::TYPE];
  $uri = entity_uri(G2::TYPE, $node);
  $ret = l($node->title, $uri['path'], $uri['options']);
  if (!empty($node->expansion)) {
    // Why t() ? Because varying languages have varying takes on spaces
    // before/after semicolons.
    $ret .= t(': @expansion', ['@expansion' => $node->expansion]);
  }
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
 * @return string
 *   Title / nid / teaser / [body].
 *
 * @todo 20110122: replace with just a node rendered with a specific view_mode
 */
function ztheme_g2_wotd($variables) {
  $node = $variables[G2::TYPE];
  if (empty($node)) {
    return NULL;
  }
  $uri = entity_uri(G2::TYPE, $node);

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

  // No need to test: won't change anything unless taxonomy has been returned.
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
        'url' => url(G2::ROUTE_FEED_WOTD, ['absolute' => TRUE]),
        // @todo Find a better title.
        'title' => t('Glossary feed'),
      ]
    );
  }

  return $ret;
}
