<?php

/**
 * @file
 * Install file for G2 Glossary.
 *
 * @copyright 2005-2015 Frédéric G. Marand, for Ouest Systemes Informatiques.
 */

use Drupal\g2\Requirements;

/* ===== Code below this line not checked for D8 ============================ */

/**
 * Implements hook_requirements().
 */
function g2_requirements($phase) {
  if ($phase != 'runtime') {
    return [];
  }

  $requirements = Requirements::create(\Drupal::getContainer());
  $requirements->checkControllers();
  $result = $requirements->getResult();
  return $result;

  // 3. Statistics req. check.
  $stats = module_exists('statistics');
  $count = variable_get('statistics_count_content_views', NULL);
  if (!$stats && !$count) {
    // This one is a (questionable) choice.
    $severity = REQUIREMENT_INFO;
    $value = t('G2 statistics disabled.');
  }
  elseif ($stats xor $count) {
    // This one is inconsistent.
    $severity = REQUIREMENT_WARNING;
    $value = t('G2 statistics incorrectly configured.');
  }
  else {
    // Both on: optimal.
    $severity = REQUIREMENT_OK;
    $value = t('G2 statistics configured correctly.');
  }

  $items = array();
  $stats_link = array(':link' => url('admin/build/modules/list'));
  $items[] = $stats
    ? t('<a href=":link">Statistics module</a> installed and activated: OK.', $stats_link)
    : t('<a href=":link">Statistics module</a> not installed or not activated.', $stats_link);
  $link_text = $count ? t('ON') : t('OFF');
  if ($stats) {
    $link = l($link_text, 'admin/reports/settings',
        array('fragment' => 'statistics_count_content_views'));
    $items[] = t('Count content views" setting is !link', array('!link' => $link));
  }
  else {
    $items[] = t('G2 relies on statistics.module to provide data for the G2 "Top" block and XML-RPC service. If you do not use either block, you can leave statistics.module disabled.');
  }
  $description = theme('item_list', $items);
  $ret['statistics'] = array(
    'title'       => t('G2 statistics'),
    'value'       => $value,
    'description' => $description,
    'severity'    => $severity,
  );
  return $ret;
}

/**
 * Implements hook_schema().
 *
 * Define the structure of the non-core tables used by G2.
 *
 * Schema API does not define it, but thes tables should have UTF-8
 * as their default charset
 */
function g2_schema() {
  $schema = array();

  /* Additional fields in G2 entries.
   *
   * G2 does not currently revision the additional information it stores
   * its entries, so it does not need to keep the vid.
   */
  $schema['g2_node'] = array(
    'fields' => array(
      'nid' => array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The node id for the current G2 entry',
      ),
      'period' => array(
        'type' => 'varchar',
        'length' => 50,
        'not null' => FALSE,
        'description' => 'A time period during which the entity of concept described by the term was in use',
      ),
      'complement' => array(
        'type' => 'text',
        'size' => 'medium',
        'not null' => FALSE,
        'description' => 'Editor-only general information about the item content',
      ),
      'origin' => array(
        'type' => 'text',
        'size' => 'medium',
        'not null' => FALSE,
        'description' => 'Editor-only intellectual property-related information about the item content',
      ),
    ),
    'primary key' => array('nid'),
    'unique keys' => array(),
    'indexes' => array(),
    'description' => 'The G2-specific, non-versioned, informations contained in G2 entry nodes in addition to default node content.',
  );

  // G2 per-node referer stats.
  $schema['g2_referer'] = array(
    'fields' => array(
      'nid' => array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The node id for the current G2 entry',
      ),
      'referer' => array(
        'type' => 'varchar',
        'length' => 128,
        'not null' => TRUE,
        'default' => '',
        'description' => 'The URL on which a link was found to the current item',
      ),
      'incoming' => array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The number of hits coming from this referer',
      ),
    ),
    'indexes' => array(),
    'primary key' => array('nid', 'referer'),
    'unique keys' => array(),
    'indexes' => array(
      'referer' => array('referer'),
    ),
    'description' => 'The referer tracking table for G2 entries',
  );

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

  $result = db_query($sql);

  $count = 0;
  while (is_object($row = db_fetch_object($result))) {
    variable_del($row->name);
    $count++;
  }
  if ($count) {
    $message = t('Removed @count G2 obsolete 4.7.x/5.x variables', array('@count' => $count));
    cache_clear_all('variables', 'cache');
  }
  else {
    $message = t('No obsolete variable to clean.');
  }
  drupal_set_message($message, status);

  /* Convert Drupal 4.7.x/5.x block deltas
   *
   * This is really only needed for sites upgrading from D5.
   */
  module_load_include('inc', 'g2', 'g2_data');
  $delta_changes = array(
    0 => G2DELTAALPHABAR,
    1 => G2DELTARANDOM,
    2 => G2DELTATOP,
    3 => G2DELTAWOTD,
    4 => G2DELTALATEST,
  );
  $sql = "UPDATE {blocks} b SET delta = '%s' WHERE module = '%s' AND delta = %d ";
  $count = 0;
  foreach ($delta_changes as $old => $new) {
    db_query($sql, $new, 'g2', $old);
    $count += db_affected_rows();
  }

  if ($count) {
    $message = t('Converted G2 block deltas to new format.');
    cache_clear_all('variables', 'cache');
  }
  else {
    $message = t('No obsolete delta to convert.');
  }

  drupal_set_message($message, 'status');
  return array();
}

/**
 * Implement hook_update_N().
 *
 * Update 6001: Convert "%" tokens from 4.7.x/5.1.[01] in the WOTD feed
 * configuration to "!".
 *
 * This is really only needed for sites upgrading from D4.7 or D5.
 */
function g2_update_6001() {
  $count = 0;
  $wotd_author = variable_get(G2VARWOTDFEEDAUTHOR, G2DEFWOTDFEEDAUTHOR);
  if (strpos($wotd_author, '%author') !== FALSE) {
    variable_set(G2VARWOTDFEEDAUTHOR, str_replace('%author', '@author', $wotd_author));
    $count++;
  }
  $wotd_descr = variable_get(G2VARWOTDFEEDDESCR, G2DEFWOTDFEEDDESCR);
  if (strpos($wotd_descr, '%site') !== FALSE) {
    variable_set(G2VARWOTDFEEDDESCR, str_replace('%site', ':site', $wotd_descr));
    $count++;
  }

  if ($count) {
    // Coder false positive: :link is filtered.
    $message = t('Replaced @count occurrences of old "percent" tokens by new "colon" ones on the <a href=":link">WOTD block feed settings</a>.', array(
      '@count' => $count,
      // Constant: no need to check_url().
      ':link'  => url('admin/build/block/configure/g2/' . G2DELTAWOTD),
    ));
  }
  else {
    $message = t('No old token to convert for the WOTD feed settings.');
  }
  drupal_set_message($message, 'status');
  return array();
}

/**
 * Implement hook_update_N().
 *
 * Update 6002: Temporarily restore the g2_referer table: unlike the D5 branch, the current
 * code in the 6.x and 7.x-1.x branches still uses it. The 7.x-2.x branch will
 * likely remove it as in D5.
 *
 * This is really only needed for sites upgrading from D5.
 */
function g2_update_6002() {
  $ret = array();
  if (!db_table_exists('g2_referer')) {
    $message = t('Temporarily reinstating g2_referer table for current version.')
     . t('In future versions, use an external tracking module instead.');
    $schema = g2_schema();
    db_create_table($ret, 'g2_referer', $schema['g2_referer']);
  }
  else {
    $message = t('g2_referer table was there. No need to recreate it.');
  }
  drupal_set_message($message, 'status');
  return $ret;
}
