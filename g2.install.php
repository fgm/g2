<?php

/**
 * @file
 * Install file for G2 Glossary.
 *
 * @copyright 2005-2023 Frédéric G. Marand, for Ouest Systemes Informatiques.
 */

use Drupal\g2\G2;
use Drupal\g2\Requirements;

/**
 * Implements hook_requirements().
 */
function g2_requirements($phase) {
  if ($phase != 'runtime') {
    return [];
  }

  $requirements = Requirements::create(\Drupal::getContainer());
  $requirements->checkControllers();
  $requirements->checkStatistics();
  $result = $requirements->getResult();
  return $result;
}

/* ===== Code below this line not checked for D8 ============================ */

/**
 * Implements hook_schema().
 *
 * Define the structure of the non-core tables used by G2.
 *
 * Schema API does not define it, but thes tables should have UTF-8
 * as their default charset
 */
function g2_schema() {
  $schema = [];

  /* Additional fields in G2 entries.
   *
   * G2 does not currently revision the additional information it stores
   * its entries, so it does not need to keep the vid.
   */
  $schema['g2_node'] = [
    'fields' => [
      'nid' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The node id for the current G2 entry',
      ],
      'period' => [
        'type' => 'varchar',
        'length' => 50,
        'not null' => FALSE,
        'description' => 'A time period during which the entity of concept described by the term was in use',
      ],
      'complement' => [
        'type' => 'text',
        'size' => 'medium',
        'not null' => FALSE,
        'description' => 'Editor-only general information about the item content',
      ],
      'origin' => [
        'type' => 'text',
        'size' => 'medium',
        'not null' => FALSE,
        'description' => 'Editor-only intellectual property-related information about the item content',
      ],
    ],
    'primary key' => ['nid'],
    'unique keys' => [],
    'indexes' => [],
    'description' => 'The G2-specific, non-versioned, informations contained in G2 entry nodes in addition to default node content.',
  ];

  // G2 per-node referer stats.
  $schema['g2_referer'] = [
    'fields' => [
      'nid' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The node id for the current G2 entry',
      ],
      'referer' => [
        'type' => 'varchar',
        'length' => 128,
        'not null' => TRUE,
        'default' => '',
        'description' => 'The URL on which a link was found to the current item',
      ],
      'incoming' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The number of hits coming from this referer',
      ],
    ],
    'indexes' => [],
    'primary key' => ['nid', 'referer'],
    'unique keys' => [],
    'indexes' => [
      'referer' => ['referer'],
    ],
    'description' => 'The referer tracking table for G2 entries',
  ];

  return $schema;
}

/**
 * Implements hook_update_N().
 *
 * Update 6000: Update the schema for Drupal 6 first version.
 * - remove g2_*[info|title] variables, which were used in block setup. Title is
 *   now managed by core, Info was really needed.
 * - have a valid schema version recorded for future updates.
 */
function g2_update_6000() {
  // Clean-up obsolete variables.
  $sql = <<<SQL
SELECT v.name
FROM {variable} v
WHERE v.name LIKE 'g2_%%info' OR v.name LIKE 'g2_%%title'
  OR v.name LIKE 'g2/%%'
SQL;

  $result = \Drupal::database()->query($sql);

  $count = 0;
  while (is_object($row = db_fetch_object($result))) {
    variable_del($row->name);
    $count++;
  }
  if ($count) {
    $message = t('Removed @count G2 obsolete 4.7.x/5.x variables', ['@count' => $count]);
    cache_clear_all('variables', 'cache');
  }
  else {
    $message = t('No obsolete variable to clean.');
  }
  \Drupal::messenger()->addStatus($message);

  /* Convert Drupal 4.7.x/5.x block deltas
   *
   * This is really only needed for sites upgrading from D5.
   */
  $delta_changes = [
    0 => G2::DELTA_ALPHABAR,
    1 => G2::DELTA_RANDOM,
    2 => G2::DELTA_TOP,
    3 => G2::DELTA_WOTD,
    4 => G2::DELTA_LATEST,
  ];
  $sql = "UPDATE {blocks} b SET delta = '%s' WHERE module = '%s' AND delta = %d ";
  $count = 0;
  foreach ($delta_changes as $old => $new) {
    \Drupal::database()->query($sql, $new, 'g2', $old);
    $count += db_affected_rows();
  }

  if ($count) {
    $message = t('Converted G2 block deltas to new format.');
    cache_clear_all('variables', 'cache');
  }
  else {
    $message = t('No obsolete delta to convert.');
  }

  \Drupal::messenger()->addStatus($message);
  return [];
}

/**
 * Implements hook_update_N().
 *
 * Update 6001: Convert "%" tokens from 4.7.x/5.1.[01] in the WOTD feed
 * configuration to "!".
 *
 * This is really only needed for sites upgrading from D4.7 or D5.
 */
function g2_update_6001() {
  $count = 0;
  $wotd_author = variable_get(G2::VARWOTDFEEDAUTHOR, G2::DEFWOTDFEEDAUTHOR);
  if (strpos($wotd_author, '%author') !== FALSE) {
    variable_set(G2::VARWOTDFEEDAUTHOR, str_replace('%author', '@author', $wotd_author));
    $count++;
  }
  $wotd_descr = variable_get(G2::VARWOTDFEEDDESCR, G2::DEFWOTDFEEDDESCR);
  if (strpos($wotd_descr, '%site') !== FALSE) {
    variable_set(G2::VARWOTDFEEDDESCR, str_replace('%site', ':site', $wotd_descr));
    $count++;
  }

  if ($count) {
    // Coder false positive: :link is filtered.
    $message = t('Replaced @count occurrences of old "percent" tokens by new "colon" ones on the <a href=":link">WOTD block feed settings</a>.', [
      '@count' => $count,
      // Constant: no need to check_url().
      ':link'  => url('admin/build/block/configure/g2/' . G2::DELTA_WOTD),
    ]);
  }
  else {
    $message = t('No old token to convert for the WOTD feed settings.');
  }
  \Drupal::messenger()->addStatus($message);
  return [];
}

/**
 * Implements hook_update_N().
 *
 * Update 6002: Temporarily restore the g2_referer table: unlike the D5 branch,
 * the current code in the 6.x and 7.x-1.x branches still uses it.
 * The 7.x-2.x branch will likely remove it as in D5.
 *
 * This is really only needed for sites upgrading from D5.
 */
function g2_update_6002() {
  $ret = [];
  if (!db_table_exists('g2_referer')) {
    $message = t('Temporarily reinstating g2_referer table for current version.')
      . t('In future versions, use an external tracking module instead.');
    $schema = g2_schema();
    db_create_table($ret, 'g2_referer', $schema['g2_referer']);
  }
  else {
    $message = t('g2_referer table was there. No need to recreate it.');
  }
  \Drupal::messenger()->addStatus($message);
  return $ret;
}
