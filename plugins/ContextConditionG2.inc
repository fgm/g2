<?php
/**
 * @file
 * Define a Context condition plugin recognizing pages under G2 control.
 *
 * PHPCS: naming cannot honor naming standards without breaking Context 3.
 */

/**
 * Expose G2-related pages as a context condition.
 */
class ContextConditionG2 extends context_condition {
  /**
   * Override context_condition::condition_values().
   */
  function condition_values() {
    $values = array();
    $values['g2_admin'] = t('G2 admin pages: settings');
    $values['g2_node'] = t('G2 node pages');
    $values['g2_user'] = t('G2 user pages: main, per-initial, entries list');
    return $values;
  }

  /**
   * Mark the condition as met.
   *
   * @param mixed $value
   *   One of the values from condition_values().
   */
  function execute($value) {
    foreach ($this->get_contexts($value) as $key => $context) {
      $this->condition_met($context, $value);
    }
  }
}
