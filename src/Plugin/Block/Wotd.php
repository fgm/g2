<?php

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wotd is the Word of the Day plugin.
 *
 * @Block(
 *   id = "g2_wotd",
 *   admin_label = @Translation("G2 Word of the day"),
 *   category = @Translation("G2")
 * )
 *
 * @state g2.wotd.date
 * @state g2.wotd.entry
 */
class Wotd extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Default for the current WOTD state entry: none.
   */
  const DEFAULT_ENTRY = 0;

  /**
   * Default G2 WOTD entry, for both block and feed. Translatable.
   *
   * @todo Check whether this should move to a WOTD service used by block+feed.
   * @todo Check whether this is redundant with the plugin title.
   */
  const DEFAULT_TITLE = 'Word of the day in the G2 glossary';

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
