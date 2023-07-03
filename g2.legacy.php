<?php

/**
 * @file
 * This is unported code for the module.
 */

declare(strict_types = 1);

use Drupal\g2\G2;

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
