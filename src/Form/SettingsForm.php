<?php

declare(strict_types = 1);

namespace Drupal\g2\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\g2\Alphabar;
use Drupal\g2\G2;
use Drupal\g2\WOTD;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingsForm contains the G2 configuration form.
 *
 * @todo Refactor like \Drupal\config_inspector\Form\ConfigInspectorItemForm.
 * @todo Relate service.alphabar.contents configuration with routes like
 *   g2.initial.
 */
class SettingsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The ID of the Alphabar contents in the form. Used for the Ajax callback.
   */
  const ALPHABAR_WRAPPER_ID = 'services-alphabar-contents-wrapper';

  /**
   * The g2.alphabar service.
   *
   * @var \Drupal\g2\Alphabar
   */
  protected Alphabar $alphabar;

  /**
   * The schema information for the module configuration.
   *
   * @var array
   */
  protected $configSchema;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * The router.builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * The g2.wotd service.
   *
   * @var \Drupal\g2\WOTD
   */
  protected $wotd;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param array $config_schema
   *   The schema array for the configuration data.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router.builder service.
   * @param \Drupal\g2\Alphabar $alphabar
   *   The g2.alphabar service.
   * @param \Drupal\g2\WOTD $wotd
   *   The g2.wotd service.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    ConfigFactoryInterface $configFactory,
    array $config_schema,
    RouteBuilderInterface $router_builder,
    Alphabar $alphabar,
    WOTD $wotd,
  ) {
    parent::__construct($configFactory);
    $this->alphabar = $alphabar;
    $this->etm = $etm;
    $this->configSchema = $config_schema;
    $this->routerBuilder = $router_builder;
    $this->wotd = $wotd;
  }

  /**
   * Extract the last part of a dotted path as used in config and state.
   *
   * @param string $name
   *   The path to extract from.
   *
   * @return string
   *   The last component.
   */
  protected function component(string $name): string {
    $ar = explode('.', $name);
    return end($ar);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $alphabar = $container->get(G2::SVC_ALPHABAR);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $etm */
    $etm = $container->get(G2::SVC_ETM);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get(G2::SVC_CONF);

    /** @var \Drupal\Core\Routing\RouteBuilderInterface $router_builder */
    $router_builder = $container->get('router.builder');

    /** @var \Drupal\Core\Config\TypedConfigManagerInterface $typed_config */
    $typed_config = $container->get('config.typed');

    /** @var \Drupal\g2\WOTD $wotd */
    $wotd = $container->get(G2::SVC_WOTD);

    return new static($etm, $config_factory, $typed_config->getDefinition(G2::CONFIG_NAME), $router_builder, $alphabar, $wotd);
  }

  /**
   * Helper for form builders : prepare top-level details.
   *
   * @param array $form
   *   The base form array.
   * @param array $schema
   *   The configuration schema from which to take the list of details.
   * @param string $section
   *   The configuration section in which to build the list of details.
   *
   * @return array
   *   The extended array.
   */
  protected static function prepareTopLevelDetails(array $form, array $schema, $section) {
    foreach ($schema as $top => $top_schema) {
      $form[$section][$top] = [
        '#type' => 'details',
        '#title' => $top_schema['label'],
      ];
    }
    return $form;
  }

  /**
   * Split a config label in two parts: title and description, if available.
   *
   * The split happens at the first "." or "?", if any.
   *
   * @param string $label
   *   The combined title and description, to be split.
   *
   * @return string[]
   *   A ['#title' => $title, '#description' => $description] array.
   */
  protected function getInfoFromLabel(string $label): array {
    // Merge a 2-element array to guarantee the destructuring assignment below.
    $exploded = array_merge(preg_split('/(\.|\?)/', $label, 2), [NULL, NULL]);
    [$title, $description] = $exploded;
    $info = [];
    if (!empty($title)) {
      $info['#title'] = $title;
    }
    if (!empty($description)) {
      $info['#description'] = $description;
    }
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [G2::CONFIG_NAME];
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
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildBlockForm(array $form, FormStateInterface $formState, array $config, array $schema) {
    $section = 'block';
    $form = $this->prepareTopLevelDetails($form, $schema, $section);
    $service_config = $this->config(G2::CONFIG_NAME)->get('services');

    $element = &$form[$section]['alphabar'];
    $element['row_length'] = [
      '#type' => 'number',
      '#title' => $schema['alphabar']['mapping']['row_length']['label'],
      '#default_value' => $config['alphabar']['row_length'],
    ];

    $element = &$form[$section]['latest'];
    $element['count'] = [
      '#type' => 'number',
      '#title' => $schema['latest']['mapping']['count']['label'],
      '#default_value' => $config['latest']['count'],
      '#max' => $service_config['latest']['max_count'],
      '#min' => 1,
    ];

    $element = &$form[$section]['random'];
    $element['max_age'] = [
      '#type' => 'number',
      '#min' => 1,
      '#default_value' => $config['random']['max_age'],
    ] + $this->getInfoFromLabel($schema['random']['mapping']['max_age']['label']);

    $path = explode('.', G2::VARTOPCOUNT);
    [$section, $group, $key] = $path;
    $element = &$form[$section][$group];
    $element[$key] = [
      '#type' => 'number',
      '#title' => $schema[$group]['mapping'][$key]['label'],
      '#default_value' => $config[$group][$key],
      '#max' => $service_config[$group]['max_count'],
      '#min' => 1,
    ];

    $element = &$form[$section]['wotd'];
    $element['wotd'] = [
      '#markup' => $this->t('<ul>
  <li>Place a G2 WOTD block in a region using the <a href=":block">block configuration page</a>,
    and configure its title and display conditions there.</li>
  <li>Use the <a href=":vm">G2 Block view display</a> on the %bundle to adjust the WOTD rendering.</li>
  <li>Configure the WOTD value and rotation on the <a href=":services">WOTD service configuration</a>.</li>
  </ul>',
        [
          ':block' => Url::fromRoute(G2::ROUTE_BLOCKS)->toString(),
          ':vm' => Url::fromRoute(G2::ROUTE_VM, [
            'node_type' => G2::BUNDLE,
            'view_mode_name' => G2::VM_BLOCK,
          ])->toString(),
          '%bundle' => G2::BUNDLE,
          ':services' => Url::fromRoute(G2::ROUTE_CONFIG_SERVICES)->toString(),
        ])
      . '</p>',
    ];

    return $form;
  }

  /**
   * Build the controllers configuration form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   *
   * @todo provide an auto-complete for routes instead of using a plain string.
   * @todo provide an auto-complete for node ids instead of using a plain
   *   number.
   */
  public function buildControllerForm(array $form, FormStateInterface $formState, array $config, array $schema) {
    $section = 'controller';
    $form = $this->prepareTopLevelDetails($form, $schema, $section);
    $this->messenger()
      ->addStatus($this->t('Be aware that saving configuration on this tab will mark the router as needing a rebuild.'));

    $element = &$form['controller']['main'];
    $element['nid'] = [
      '#type' => 'number',
      '#default_value' => $config['main']['nid'],
      '#element_validate' => [[$this, 'validateControllerMainNid']],
    ] + $this->getInfoFromLabel($schema['main']['mapping']['nid']['label']);

    foreach ([
      'main' => G2::ROUTE_AUTOCOMPLETE_ROUTE_0PARAM,
      'initial' => G2::ROUTE_AUTOCOMPLETE_ROUTE_1PARAM,
      'homonyms' => G2::ROUTE_AUTOCOMPLETE_ROUTE_1PARAM,
    ] as $name => $route) {
      $element = &$form['controller'][$name];
      $element['route'] = [
        '#type' => 'textfield',
        '#autocomplete_route_name' => $route,
        '#default_value' => $config[$name]['route'],
      ] + $this->getInfoFromLabel($schema[$name]['mapping']['route']['label']);
    }

    $path = explode('.', G2::VARLOGREFERRERS);
    [$section, $group, $key] = $path;
    $element = &$form[$section][$group];
    $element[$key] = [
      '#type' => 'checkbox',
      '#default_value' => $config[$group][$key],
    ] + $this->getInfoFromLabel($schema[$group]['mapping'][$key]['label']);

    $element = &$form['controller']['homonyms'];
    $element['redirect_on_single_match'] = [
      '#type' => 'checkbox',
      '#default_value' => $config['homonyms']['redirect_on_single_match'],
    ] + $this->getInfoFromLabel($schema['homonyms']['mapping']['redirect_on_single_match']['label']);

    $redirects = [
      Response::HTTP_MOVED_PERMANENTLY,
      Response::HTTP_FOUND,
      Response::HTTP_TEMPORARY_REDIRECT,
      Response::HTTP_PERMANENTLY_REDIRECT,
    ];
    $options = [];
    foreach ($redirects as $redirect) {
      $options[$redirect] = $this->t(
        '@status: @text',
        [
          '@status' => $redirect,
          '@text' => Response::$statusTexts[$redirect],
        ]
      );
    }
    $element['redirect_status'] = [
      '#type' => 'radios',
      '#title' => $schema['homonyms']['mapping']['redirect_status']['label'],
      '#options' => $options,
      '#default_value' => $config['homonyms']['redirect_status'],
    ];

    $view_ids = $this->etm
      ->getStorage('view')
      ->getQuery()
      ->condition('tag', 'G2')
      ->execute();
    $views = $this->etm->getStorage('view')->loadMultiple($view_ids);
    $options = ['' => $this->t('-- Use plain node list --')];
    /** @var \Drupal\views\ViewEntityInterface $view */
    foreach ($views as $vid => $view) {
      $options[$vid] = $view->label();
    }
    $element['vid'] = [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $config['homonyms']['vid'],
    ] + $this->getInfoFromLabel($schema['homonyms']['mapping']['vid']['label']);
    $element['nid'] = [
      '#type' => 'number',
      '#default_value' => $config['homonyms']['nid'],
    ] + $this->getInfoFromLabel($schema['homonyms']['mapping']['nid']['label']);

    $element = &$form['controller']['wotd'];
    $element['controller'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Configure the WOTD feed by editing the <a href=":view"><code>g2_wotd</code> view</a> and access it on <a href=":url">:url</a>', [
        ':view' => Url::fromRoute('entity.view.edit_display_form', [
          'view' => G2::VIEW_WOTD,
          'display_id' => G2::VIEW_WOTD_DISPLAY,
        ])->toString(),
        ':url' => Url::fromRoute(G2::ROUTE_FEED_WOTD)->toString(),
      ]),
    ];
    return $form;
  }

  /**
   * Build the formatting configuration form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildFormattingForm(array $form, FormStateInterface $formState, array $config, array $schema) {
    [, $element] = explode('.', G2::VARNOFREETAGGING);
    $form['formatting'][$element] = [
      '#type' => 'checkbox',
      '#title' => $schema[$element]['label'],
      '#default_value' => $config[$element],
    ];

    [, $element] = explode('.', G2::VARTOOLTIPS);
    $form['formatting'][$element] = [
      '#type' => 'select',
      '#options' => [
        G2::TOOLTIPS_NONE => $this->t('None'),
        G2::TOOLTIPS_TITLES => $this->t('Titles'),
        G2::TOOLTIPS_TEASERS => $this->t('Teasers'),
      ],
      '#default_value' => $config[$element],
      '#element_validate' => [[$this, 'validateFormattingTooltips']],
    ] + $this->getInfoFromLabel($schema[$element]['label']);

    [, $element] = explode('.', G2::VARPAGETITLE);
    $form['formatting'][$element] = [
      '#type' => 'textfield',
      '#default_value' => $config[$element],
    ] + $this->getInfoFromLabel($schema[$element]['label']);

    return $form;
  }

  /**
   * Build the API configuration form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildApiForm(array $form, FormStateInterface $formState, array $config, array $schema) {
    $form['api']['help'] = [
      '#markup' => '<p>'
      . $this->t('Configure the G2 API client and server if needed.
The client enables your site to provide links using a remote G2 instance on
another site, while the server allows your site to provide entries to such
  client sites.')
      . '</p>',
    ];
    $form['api']['client'] = [
      '#type' => 'details',
      '#title' => $schema['client']['label'],
    ];
    $form['api']['client']['remote'] = [
      '#type' => 'textfield',
      '#default_value' => $config['client']['remote'],
      '#element_validate' => [[$this, 'validateApiRemote']],
    ] + $this->getInfoFromLabel($schema['client']['mapping']['remote']['label']);

    $form['api']['server'] = [
      '#type' => 'details',
      '#title' => $schema['server']['label'],
    ];
    $form['api']['server']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $schema['server']['mapping']['enabled']['label'],
      '#default_value' => $config['server']['enabled'],
    ];
    $form['api']['server']['throttle'] = [
      '#type' => 'range',
      '#default_value' => $config['server']['throttle'],
      '#max' => 1.0,
      '#min' => 0.0,
      '#step' => 0.1,
      '#element_validate' => [[$this, 'validateApiThrottle']],
    ] + $this->getInfoFromLabel($schema['server']['mapping']['throttle']['label']);

    $form['api']['version'] = [
      '#markup' => '<p>'
      . $this->t('This site is running G2 version %version. See the <a href=":page" title="G2 Glossary project page">G2 project page</a> on Drupal.org.', [
        '%version' => G2::VERSION,
        ':page' => 'http://drupal.org/project/g2',
      ])
      . '</p>',
    ];

    return $form;
  }

  /**
   * Build the services configuration form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $config
   *   The configuration for which to build a form.
   * @param array $schema
   *   The schema of the configuration for which to build a form.
   *
   * @return array
   *   The form array.
   */
  public function buildServicesForm(array $form, FormStateInterface $formState, array $config, array $schema) {
    $section = 'services';
    $form = $this->prepareTopLevelDetails($form, $schema, $section);

    $wrapperID = str_replace('.', '-', G2::VARALPHABARCONTENTS) . '-wrapper';
    $element = &$form[$section]['alphabar'];
    $element['contents'] = [
      '#type' => 'textfield',
      '#title' => $schema['alphabar']['mapping']['contents']['label'],
      '#default_value' => $config['alphabar']['contents'],
      '#prefix' => "<div id='${wrapperID}'>",
      '#suffix' => '</div>',
    ];
    $element['generate'] = [
      '#type' => 'button',
      '#value' => $this->t('Rebuild from existing G2 entries'),
      '#name' => 'generate',
      '#ajax' => [
        'callback' => '::generateAlphabar',
        'disable-refocus' => TRUE,
        'wrapper' => $wrapperID,
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Scanning G2 entries for their initials.'),
        ],
      ],
    ];

    $element = &$form[$section]['random'];
    $key = $this->component(G2::VARRANDOMSTORE);
    $element[$key] = [
      '#type' => 'checkbox',
      '#default_value' => $config['random'][$key],
    ] + $this->getInfoFromLabel($schema['random']['mapping'][$key]['label']);

    foreach (['latest', 'top'] as $service) {
      $element = &$form[$section][$service];
      $element['max_count'] = [
        '#type' => 'number',
        '#default_value' => $config[$service]['max_count'],
        '#min' => 1,
      ] + $this->getInfoFromLabel($schema[$service]['mapping']['max_count']['label']);

      $element = &$form[$section]['wotd'];
      $wotd = $this->wotd->get();
      $key = $this->component(G2::VARWOTDENTRY);
      $element[$key] = [
        '#type' => 'textfield',
        '#title' => $schema['wotd']['mapping'][$key]['label'],
        '#autocomplete_route_name' => G2::ROUTE_AUTOCOMPLETE_ENTRY,
        '#maxlength' => 60,
        '#required' => FALSE,
        '#default_value' => $this->wotd->numberedTitleInput($wotd),
        '#element_validate' => [[$this, 'validateServicesWordOfTheDay']],
      ];

      $key = $this->component(G2::VARWOTDAUTOCHANGE);
      $element[$key] = [
        '#type' => 'checkbox',
        '#default_value' => $config['wotd'][$key],
      ] + $this->getInfoFromLabel($schema['wotd']['mapping'][$key]['label']);
    }
    return $form;
  }

  /**
   * Build the settings form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The configuration for which to build a form.
   * @param \Drupal\Core\Routing\RouteMatchInterface|null $route
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
    $form['#attached']['library'][] = G2::LIB_ADMIN;

    $builder = 'build' . ucfirst($section) . 'Form';
    if (method_exists($this, $builder)) {
      $config = $this->config(G2::CONFIG_NAME)->get($section);
      $schema = $this->configSchema['mapping'][$section]['mapping'];
      $form = $this->{$builder}($form, $form_state, $config, $schema);
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * AJAX callback for Services/Alphabar form "Generate from G2 entries" button.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function generateAlphabar(array &$form, FormStateInterface $formState, Request $request) {
    $trigger = $formState->getTriggeringElement();
    if (($trigger['#name'] ?? '') !== 'generate') {
      return NULL;
    }
    $entries = $this->alphabar->fromEntries();
    $contents = implode(array_keys($entries));
    $element = $form['services']['alphabar']['contents'];
    $element['#value'] = $contents;
    $element['#description'] = $this->t('Alphabar regenerated. You can still update it before submitting the form.');
    $res = new AjaxResponse();
    $res->addCommand(new ReplaceCommand(NULL, $element));
    return $res;
  }

  /**
   * Additional submit handler for the controller configuration form.
   */
  public function submitControllerForm(array &$form, FormStateInterface $form_state) {
    $this->routerBuilder->setRebuildNeeded();
    $this->messenger()->addStatus($this->t('The router has been marked for rebuilding.'));
  }

  /**
   * Additional submit handler for the services configuration form.
   */
  public function submitServicesForm(array &$form, FormStateInterface $form_state) {
    G2::invalidateWotdView();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $section = $values['section'];
    $values = $values[$section];
    $this->configFactory()
      ->getEditable(G2::CONFIG_NAME)
      ->set($section, $values)
      ->save();

    $handler = 'submit' . ucfirst($section) . 'Form';
    if (method_exists($this, $handler)) {
      $this->{$handler}($form, $form_state);
    }

    $this->messenger()
      ->addStatus($this->t('The configuration options have been saved.'));
  }

  /**
   * Callback for #elementValidate on controller main nid.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $form
   *   The complete form.
   *
   * @return array
   *   The modified element.
   */
  public function validateControllerMainNid(
    array &$element,
    FormStateInterface $formState,
    array $form,
  ) {
    $path = explode('.', G2::VARMAINNID);
    [$section, $group, $key] = $path;
    $nid = (int) $formState->getValue($path);
    if (empty($nid)) {
      $element['#value'] = 0;
      return $element;
    }

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->etm
      ->getStorage(G2::TYPE)
      ->load($nid);
    if (empty($node) || $node->status->value == NodeInterface::PUBLISHED) {
      $formState->setError($form[$section][$group][$key],
        $this->t('The node chosen for the main page must be a valid unpublished one, or 0: "@nid" does not satisfy these requirements.', ['@nid' => $nid]));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Remove non-input elements.
    $form_state->unsetValue(['services', 'alphabar', 'generate']);
    parent::validateForm($form, $form_state);
  }

  /**
   * Callback for #elementValidate on formatting tooltips.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $form
   *   The complete form.
   *
   * @return array
   *   The modified element.
   */
  public function validateFormattingTooltips(
    array &$element,
    FormStateInterface $formState,
    array $form,
  ) {
    $ttPath = explode('.', G2::VARTOOLTIPS);
    [$section, $key] = $ttPath;
    $level = (int) $formState->getValue($ttPath);
    if (empty($level)) {
      $element['#value'] = 0;
      return $element;
    }

    $remote = $this->configFactory->get(G2::CONFIG_NAME)->get(G2::VARREMOTEG2);
    if (!empty($remote)) {
      $formState->setError($form[$section][$key],
        $this->t('Tooltips are only available on local glossaries, but this G2 glossary is <a href=":admin">configured</a> to use the remote glossary at <a href=":remote">:remote</a>.', [
          ':admin' => Url::fromRoute(G2::ROUTE_CONFIG_API)->toString(),
          ':remote' => $remote,
        ]));
    }
    return $element;
  }

  /**
   * Callback for #elementValidate on API remote client URL.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $form
   *   The complete form.
   *
   * @return array
   *   The modified element.
   */
  public function validateApiRemote(
    array &$element,
    FormStateInterface $formState,
    array $form,
  ) {
    $remPath = explode('.', G2::VARREMOTEG2);
    [$section, $group, $key] = $remPath;
    $url = $formState->getValue($remPath);
    if (empty($url)) {
      $element['#value'] = NULL;
      return $element;
    }

    $tt = $this->configFactory->get(G2::CONFIG_NAME)->get(G2::VARTOOLTIPS);
    if (!empty($tt)) {
      $formState->setError($form[$section][$group][$key],
        $this->t('This configuration attempts to enable the G2 remote client,
          although tooltips are <a href=":admin">configured</a>.', [
            ':admin' => Url::fromRoute(G2::ROUTE_CONFIG_FORMATTING)->toString(),
          ]
      ));
    }
    return $element;
  }

  /**
   * Callback for #elementValidate on API remote server throttle.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $form
   *   The complete form.
   *
   * @return array
   *   The modified element.
   */
  public function validateApiThrottle(
    array &$element,
    FormStateInterface $formState,
    array $form,
  ) {
    // Sanitize API throttle.
    $tPath = explode('.', G2::VARAPITHROTTLE);
    $throttle = (float) $formState->getValue($tPath);
    $formState->setValueForElement($element, $throttle);

    return $element;
  }

  /**
   * Callback for #elementValidate on WOTD entry.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $form
   *   The complete form.
   *
   * @return array
   *   The modified element.
   */
  public function validateServicesWordOfTheDay(
    array &$element,
    FormStateInterface $formState,
    array $form,
  ): array {
    $path = explode('.', G2::VARWOTDENTRY);
    $value = $formState->getValue($path);
    if (empty($value)) {
      $element['#value'] = '';
      return $element;
    }
    $nodes = $this->wotd->matchesFromTitle($value);
    if (empty($nodes)) {
      return $element;
    }
    $node = current($nodes);
    $nid = (int) $node->id();
    $formState->setValueForElement($element, $nid);
    return $element;
  }

}
