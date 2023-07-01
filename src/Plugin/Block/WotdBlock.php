<?php

declare(strict_types = 1);

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
  public function build() {
    $viewBuilder = $this->etm->getViewBuilder(G2::TYPE);
    $entry = $this->wotd->get();
    if (empty($entry)) {
      return NULL;
    }
    $build = $viewBuilder->view($entry, G2::VM_BLOCK);
    $build['#cache'] = ['max-age' => 0];
    return $build;
  }

}
