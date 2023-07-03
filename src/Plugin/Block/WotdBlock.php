<?php

declare(strict_types = 1);

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\g2\WOTD;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wotd is the Word of the Day plugin.
 *
 * @Block(
 *   id = "g2_wotd",
 *   admin_label = @Translation("G2 Word of the day"),
 *   category = @Translation("G2"),
 *   help = @Translation("This block displays a once-a-day entry from the G2 glossary."),
 * )
 *
 * @state g2.wotd.date
 * @state g2.wotd.entry
 */
class WotdBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The block setting controlling the RSS feed icon presence.
   */
  const FEED_SETTING = 'feed_icon';

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The g2.wotd service.
   *
   * @var \Drupal\g2\WOTD
   */
  protected WOTD $wotd;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManagerInterface $etm,
    WOTD $wotd,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->etm = $etm;
    $this->wotd = $wotd;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $etm */
    $etm = $container->get(G2::SVC_ETM);
    /** @var \Drupal\g2\WOTD $wotd */
    $wotd = $container->get(G2::SVC_WOTD);
    return new static($configuration, $plugin_id, $plugin_definition,
      $etm, $wotd,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form[static::FEED_SETTING] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Display feed icon"),
      '#default_value' => $this->configuration[static::FEED_SETTING] ?? TRUE,
      '#description' => $this->t('Add the standard RSS feed icon at the bottom of the block, linking to the WOTD feed'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration[static::FEED_SETTING] = $form_state->getValue(static::FEED_SETTING);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $viewBuilder = $this->etm->getViewBuilder(G2::TYPE);
    $showFeedIcon = $this->configuration[static::FEED_SETTING] ?? TRUE;
    $entry = $this->wotd->get();
    if (empty($entry)) {
      return NULL;
    }
    $build = $viewBuilder->view($entry, G2::VM_BLOCK);
    $build['#cache'] = ['max-age' => 0];
    if ($showFeedIcon) {
      [,,$displayName] = explode('.', G2::ROUTE_FEED_WOTD);
      /** @var \Drupal\views\ViewExecutable $view */
      $display = $this->etm
        ->getStorage('view')
        ->load(G2::VIEW_WOTD)
        ->getDisplay($displayName);
      $title = $display['display_title'];
      $description = $display['display_options']['display_description'];

      $build[static::FEED_SETTING] = [
        '#theme' => 'feed_icon',
        '#url' => Url::fromRoute(G2::ROUTE_FEED_WOTD),
        '#title' => $title,
        // Ignored by #3371937, hence the need for g2_preprocess_feed_icon.
        '#attributes' => [
          'class' => ['g2-feed-icon'],
          'title' => $description,
        ],
        '#weight' => 15,
      ];
    }
    return $build;
  }

}
