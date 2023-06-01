<?php

namespace Drupal\g2\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\g2\G2;
use Drupal\g2\Random;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Random contains the G2 Random block plugin.
 *
 * Note that, since it is not cacheable, in order to actually display changing
 * content, it prevents page-level caching.
 *
 * @Block(
 *   id = "g2_random",
 *   admin_label = @Translation("G2 Random"),
 *   category = @Translation("G2"),
 *   help = @Translation("This block displays a pseudo-random entry from the G2 glossary."),
 * )
 *
 * @state g2.random.entry
 */
class RandomBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The g2.random service.
   *
   * @var \Drupal\g2\Random
   */
  protected Random $random;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManagerInterface $etm,
    Random $random,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->etm = $etm;
    $this->random = $random;
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
    $etm = $container->get(G2::SVC_ETM);
    $random = $container->get(G2::SVC_RANDOM);
    return new static($configuration, $plugin_id, $plugin_definition, $etm, $random);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $viewBuilder = $this->etm->getViewBuilder(G2::TYPE);
    $random = $this->random->get();
    $build = $viewBuilder->view($random, G2::VM_G2_BLOCK);
    return $build;
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheMaxAge() {
    // Rate-limit changes to 5 seconds as part of DoS protection.
    return 5;
  }

}
