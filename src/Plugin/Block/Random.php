<?php

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Random contains the G2 Random block plugin.
 *
 * @Block(
 *   id = "g2_random",
 *   admin_label = @Translation("G2 Random"),
 *   category = @Translation("G2")
 * )
 *
 * @state g2.random.entry
 */
class Random extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The default (state) random entry displayed by the block: none.
   */
  const DEFAULT_RANDOM_ENTRY = '';

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
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
    return new static($configuration, $plugin_id, $plugin_definition);
  }

}
