<?php
/**
 * @file
 * Define a Context reaction plugin enabling G2-specific page templates.
 *
 * PHPCS: naming cannot honor naming standards without breaking Context 3.
 */

/**
 * Expose themes as context reactions.
 */
class ContextReactionG2Template extends context_reaction_theme {

  /**
   * Override context_reaction_theme::options_form()
   */
  function options_form($context) {
    $form = array();
    $form['info'] = array(
      '#type' => 'markup',
      '#markup' => t('Enable the page-g2 page template'),
    );

    $form['g2-template'] = array(
      '#type' => 'value',
      '#value' => TRUE,
    );
    return $form;
  }

  /**
   * Override context_reaction::options_form_submit()
   */
  function options_form_submit($values) {
    return array('g2-template' => 1);
  }

  /**
   * Enable additional template.
   */
  function execute(&$vars) {
    foreach ($this->get_contexts() as $name => $context) {
      if (!empty($context->reactions['g2_template']['g2-template']) && !in_array('page-g2', $vars['theme_hook_suggestions'])) {
        $vars['theme_hook_suggestions'][] = 'page__g2';
      }
    }
  }
}
