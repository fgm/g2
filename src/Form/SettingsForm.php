<?php

/**
 * @file
 * Contains the G2 settings form.
 */

namespace Drupal\g2\Form;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class SettingsForm contains the G2 configuration form.
 *
 * @TODO Refactor like \Drupal\config_inspector\Form\ConfigInspectorItemForm.
 */
class SettingsForm extends ConfigFormBase {
  const CONFIG_NAME = 'g2.settings';

  protected $configSchema;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param array $config_schema
   *   The schema array for the configuration data.
   */
  public function __construct(ConfigFactoryInterface $config_factory, array $config_schema) {
    parent::__construct($config_factory);
    $this->configSchema = $config_schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Config\ConfigFactoryInterface  $config_factory */
    $config_factory = $container->get('config.factory');

    /* @var \Drupal\Core\Config\TypedConfigManagerInterface $typed_config */
    $typed_config = $container->get('config.typed');

    return new static($config_factory, $typed_config->getDefinition(static::CONFIG_NAME)
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [static::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $match = $this->getRouteMatch();
    $section = $match->getParameter('section');
    $result = 'g2_settings-' . $section;
    return $result;
  }

  /**
   * Build the blocks configuration form.
   *
   * @param array $form
   *   The form array.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildBlockForm(array $form, array $config, array $schema) {
    foreach ($schema as $section => $section_schema) {
      $form['block'][$section] = [
        '#type' => 'details',
        '#title' => $section_schema['label'],
      ];
    }

    $element = &$form['block']['alphabar'];
    $element['row_length'] = [
      '#type' => 'number',
      '#title' => $schema['alphabar']['mapping']['row_length']['label'],
      '#default_value' => $config['alphabar']['row_length'],
    ];

    $element = &$form['block']['latest'];
    $element['count'] = [
      '#type' => 'number',
      '#title' => $schema['latest']['mapping']['count']['label'],
      '#default_value' => $config['latest']['count'],
    ];

    $element = &$form['block']['random'];
    $element['show_terms'] = [
      '#type' => 'checkbox',
      '#title' => $schema['random']['mapping']['show_terms']['label'],
      '#default_value' => $config['random']['show_terms'],
    ];
    $element['store'] = [
      '#type' => 'checkbox',
      '#title' => $schema['random']['mapping']['store']['label'],
      '#default_value' => $config['random']['store'],
    ];

    $element = &$form['block']['top'];
    $element['count'] = [
      '#type' => 'number',
      '#title' => $schema['top']['mapping']['count']['label'],
      '#default_value' => $config['top']['count'],
    ];

    $element = &$form['block']['wotd'];
    $element['auto_change'] = [
      '#type' => 'checkbox',
      '#title' => $schema['wotd']['mapping']['auto_change']['label'],
      '#default_value' => $config['wotd']['auto_change'],
    ];
    $element['body_size'] = [
      '#type' => 'number',
      '#title' => $schema['wotd']['mapping']['body_size']['label'],
      '#default_value' => $config['wotd']['body_size'],
    ];
    $element['links'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $schema['wotd']['mapping']['links']['label'],
    ];
    foreach ($config['wotd']['links'] as $name => $value) {
      $element['links'][$name] = [
        '#type' => 'checkbox',
        '#title' => $schema['wotd']['mapping']['links']['mapping'][$name]['label'],
        '#default_value' => $value,
      ];
    }

    return $form;
  }

  /**
   * Build the controllers configuration form.
   *
   * @param array $form
   *   The form array.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   *
   * @TODO provide an auto-complete for routes instead of using a plain string.
   * @TODO provide an auto-complete for node ids instead of using a plain number.
   */
  public function buildControllerForm(array $form, array $config, array $schema) {
    foreach ($schema as $section => $section_schema) {
      $form['controller'][$section] = [
        '#type' => 'details',
        '#title' => $section_schema['label'],
      ];
    }

    $element = &$form['controller']['main'];
    $element['nid'] = [
      '#type' => 'number',
      '#title' => $schema['main']['mapping']['nid']['label'],
      '#default_value' => $config['main']['nid']
    ];

    foreach (['main', 'entries', 'initial', 'adder', 'homonyms'] as $section) {
      $element = &$form['controller'][$section];
      $element['route'] = [
        '#type' => 'textfield',
        '#title' => $schema[$section]['mapping']['route']['label'],
        '#default_value' => $config[$section]['route']
      ];
    }

    $element = &$form['controller']['homonyms'];
    $element['redirect_on_single_match'] = [
      '#type' => 'checkbox',
      '#title' => $schema['homonyms']['mapping']['redirect_on_single_match']['label'],
      '#default_value' => $config['homonyms']['redirect_on_single_match']
    ];
    $element['nid'] = [
      '#type' => 'number',
      '#title' => $schema['homonyms']['mapping']['nid']['label'],
      '#default_value' => $config['homonyms']['nid']
    ];
    $redirects = [
      Response::HTTP_MOVED_PERMANENTLY,
      Response::HTTP_FOUND,
      Response::HTTP_TEMPORARY_REDIRECT,
      Response::HTTP_PERMANENTLY_REDIRECT,
    ];
    $options = [];
    foreach ($redirects as $redirect) {
      $options[$redirect] = t('@status: @text', [
        '@status' => $redirect,
        '@text' => Response::$statusTexts[$redirect],
      ]);
    }
    $element['redirect_status'] = [
      '#type' => 'select',
      '#title' => $schema['homonyms']['mapping']['redirect_status']['label'],
      '#options' => $options,
      '#default_value' => $config['homonyms']['redirect_status']
    ];

    $vocabularies = Vocabulary::loadMultiple();
    $options = ['' => t('-- Not set --')];
    foreach ($vocabularies as $vid => $vocabulary) {
      $options[$vid] = $vocabulary->label();
    }
    $element['vid'] = [
      '#type' => 'select',
      '#title' => $schema['homonyms']['mapping']['vid']['label'],
      '#options' => $options,
      '#default_value' => $config['homonyms']['vid']
    ];

    $element = &$form['controller']['wotd'];
    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $schema['wotd']['mapping']['title']['label'],
      '#default_value' => $config['wotd']['title']
    ];
    $element['description'] = [
      '#type' => 'textfield',
      '#title' => $schema['wotd']['mapping']['description']['label'],
      '#default_value' => $config['wotd']['description']
    ];
    $element['feed_author'] = [
      '#type' => 'checkbox',
      '#title' => $schema['wotd']['mapping']['feed_author']['label'],
      '#default_value' => $config['wotd']['feed_author']
    ];

    return $form;
  }

  /**
   * Build the formatting configuration form.
   *
   * @param array $form
   *   The form array.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildFormattingForm(array $form, array $config, array $schema) {
    $element = 'hidden_extra_title';
    $form['formatting'][$element] = [
      '#type' => 'checkbox',
      '#title' => $schema[$element]['label'],
      '#default_value' => $config[$element],
    ];

    $element = 'hide_free_tagging';
    $form['formatting'][$element] = [
      '#type' => 'checkbox',
      '#title' => $schema[$element]['label'],
      '#default_value' => $config[$element],
    ];

    $element = 'tooltips_level';
    $form['formatting'][$element] = [
      '#type' => 'select',
      '#title' => $schema[$element]['label'],
      '#options' => [
        0 => t('None'),
        1 => t('Titles'),
        2 => t('Teasers'),
      ],
      '#default_value' => $config[$element],
    ];

    $element = 'title';
    $form['formatting'][$element] = [
      '#type' => 'textfield',
      '#title' => $schema[$element]['label'],
      '#default_value' => $config[$element],
    ];

    return $form;
  }

  /**
   * Build the RPC configuration form.
   *
   * @param array $form
   *   The form array.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildRpcForm(array $form, array $config, array $schema) {
    $form['rpc']['client'] = [
      '#type' => 'details',
      '#title' => $schema['client']['label'],
    ];
    $form['rpc']['client']['remote'] = [
      '#type' => 'textfield',
      '#title' => $schema['client']['mapping']['remote']['label'],
      '#default_value' => $config['client']['remote'],
    ];

    $form['rpc']['server'] = [
      '#type' => 'details',
      '#title' => $schema['server']['label'],
    ];
    $form['rpc']['server']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $schema['server']['mapping']['enabled']['label'],
      '#default_value' => $config['server']['enabled'],
    ];
    $form['rpc']['server']['throttle'] = [
      '#type' => 'number',
      '#title' => $schema['server']['mapping']['throttle']['label'],
      '#default_value' => $config['server']['throttle'],
    ];

    $form['rpc']['local'] = [
      '#type' => 'textfield',
      '#title' => $schema['local']['label'],
      '#default_value' => $config['local'],
    ];
    return $form;
  }

  /**
   * Build the services configuration form.
   *
   * @param array $form
   *   The form array.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildServiceForm(array $form, array $config, array $schema) {
    $form['service']['alphabar'] = [
      '#type' => 'details',
      '#title' => $schema['alphabar']['label'],
    ];
    $form['service']['alphabar']['contents'] = [
      '#type' => 'textfield',
      '#title' => $schema['alphabar']['mapping']['contents']['label'],
      '#default_value' => $config['alphabar']['contents'],
    ];

    return $form;
  }

  /**
   * Build the settings form.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The configuration for which to build a form.
   * @param RouteMatchInterface|NULL $route
   *   The current_route_match service.
   *
   * @return array
   *   The build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, RouteMatchInterface $route = NULL) {
    $section = $route->getParameter('section');
    $form['#tree'] = TRUE;
    $form['section'] = [
      '#type' => 'value',
      '#value' => $section,
    ];
    $builder = 'build' . ucfirst($section) . 'Form';
    if (method_exists($this, $builder)) {
      $config = $this->config(static::CONFIG_NAME)->get($section);
      $schema = $this->configSchema['mapping'][$section]['mapping'];
      $form = $this->{$builder}($form, $config, $schema);
    }
    else {
      drupal_set_message(t('Non-existent form section %section.', ['%section' => $section]), 'warning');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $section = $values['section'];
    $values = $values[$section];
    $config = $this->config(static::CONFIG_NAME);
    $config->set($section, $values)->save();
    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
