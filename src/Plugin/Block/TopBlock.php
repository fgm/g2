<?php

declare(strict_types = 1);

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\g2\G2;
use Drupal\g2\Top;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TopBlock is the Top(n) block plugin.
 *
 * @Block(
 *   id = "g2_top",
 *   admin_label = @Translation("G2 Top(n)"),
 *   category = @Translation("G2"),
 *   help = @Translation("This block displays a list of the most viewed entries in the G2 glossary."),
 * )
 */
class TopBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The g2.settings/block.top configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The g2.top service.
   *
   * @var \Drupal\g2\Top
   */
  protected $top;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $plugin_id
   *   The block ID.
   * @param array $plugin_definition
   *   The block definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The core config.factory service.
   * @param \Drupal\g2\Top $top
   *   The g2.top service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    ConfigFactoryInterface $configFactory,
    Top $top,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->top = $top;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!($this->top->isAvailable())) {
      return [];
    }
    $config = $this->configFactory->get(G2::CONFIG_NAME);
    $blockCount = $config->get(G2::VARTOPCOUNT);
    $links = $this->top->getLinks($blockCount);

    $result = [
      '#theme' => 'item_list',
      '#items' => $links,
    ];
    return $result;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    /** @var \Drupal\g2\Top $top */
    $top = $container->get(G2::SVC_TOP);

    /** @var \Drupal\Core\Config\ConfigFactory $configFactory */
    $configFactory = $container->get(G2::SVC_CONF);

    return new static($configuration, $plugin_id, $plugin_definition, $configFactory, $top);
  }

}
