<?php

declare(strict_types = 1);

namespace Drupal\g2\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\State\StateInterface;
use Drupal\g2\G2;
use Drupal\g2\WOTD as Service;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * WOTD argument default plugin.
 *
 * @ViewsArgumentDefault(
 *   id = "g2_wotd",
 *   title = @Translation("G2 word of the day")
 * )
 */
class WOTD extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The maximum age for a word of the day is one day.
   */
  const MAX_AGE = 86400;

  /**
   * The g2.wotd service.
   *
   * @var \Drupal\g2\WOTD
   */
  protected Service $wotd;

  /**
   * The code state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Constructs a new WOTD instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\g2\WOTD $wotd
   *   The g2.wotd service.
   * @param \Drupal\Core\State\State $state
   *   The code state service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    Service $wotd,
    StateInterface $state,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
    $this->wotd = $wotd;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get(G2::SVC_WOTD),
      $container->get(G2::SVC_STATE),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument(): int {
    $wotd = $this->wotd->get();
    $nid = $wotd instanceof NodeInterface
      ? (int) $wotd->id()
      : 0;
    return $nid;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    $wotd = $this->wotd->get();
    $age = static::MAX_AGE;
    if ($wotd instanceof NodeInterface) {
      $age = $wotd->getCacheMaxAge();
    }
    if ($age < 0 || $age > static::MAX_AGE) {
      $age = static::MAX_AGE;
    }
    return $age;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    $wotd = $this->wotd->get();
    $contexts = $wotd instanceof NodeInterface
      ? $wotd->getCacheContexts()
      : [];
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    $wotd = $this->wotd->get();
    $tags = $wotd instanceof NodeInterface
      ? $wotd->getCacheTags()
      : [];
    return $tags;
  }

}
