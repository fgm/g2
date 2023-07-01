<?php

declare(strict_types = 1);

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\g2\G2;
use Drupal\g2\Latest;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LatestBlock is the Latest(n) block plugin.
 *
 * @Block(
 *   id = "g2_latest",
 *   admin_label = @Translation("G2 Latest(n)"),
 *   category = @Translation("G2"),
 *   help = @Translation("This block displays a list of the most recently
 *   updated entries in the G2 glossary."),
 * )
 */
class LatestBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The g2.latest service.
   *
   * @var \Drupal\g2\Latest
   */
  protected $latest;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $etm,
    Latest $latest,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->etm = $etm;
    $this->latest = $latest;
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

    /** @var \Drupal\g2\Latest $latest */
    $latest = $container->get(G2::SVC_LATEST);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $container->get(G2::SVC_CONF);
    return new static($configuration, $plugin_id, $plugin_definition, $configFactory, $etm, $latest);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $count = $this->configFactory
      ->get(G2::CONFIG_NAME)
      ->get(G2::VARLATESTCOUNT);
    $links = $this->latest->getLinks($count);
    $md = (new CacheableMetadata())
      ->addCacheTags([
        "config:" . (G2::CONFIG_NAME),
        "node_list:" . (G2::BUNDLE),
      ]);
    /** @var \Drupal\Core\Link $link */
    foreach ($links as $link) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $link->getUrl()->getOption('entity');
      $md->addCacheableDependency($node);
    }
    $build = [
      '#theme' => 'item_list',
      '#items' => $links,
    ];
    $md->applyTo($build);
    return $build;
  }

}
