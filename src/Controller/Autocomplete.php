<?php

namespace Drupal\g2\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\g2\G2;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Autocomplete contains the node by title autocomplete controller.
 */
class Autocomplete implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * Autocomplete constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity.manager service.
   * @param array $config
   *   The g2.settings/controller.homonyms configuration.
   */
  public function __construct(EntityTypeManagerInterface $etm, array $config) {
    $this->etm = $etm;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');

    $config = $config_factory->get(G2::CONFIG_NAME)->get('controller.homonyms');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get('entity_type.manager');
    return new static($entity_manager, $config);
  }

  /**
   * Controller for g2.autocomplete.
   *
   * @param \Drupal\node\NodeInterface[] $g2_match
   *   Unsafe. The entry for which to find matching G2 entries.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The nodes matching the passed title portion.
   */
  public function indexAction(array $g2_match) {
    $matches = [];
    foreach ($g2_match as $nid => $node) {
      $args = [
        '@title' => $node->getTitle(),
        '@nid' => $node->id(),
      ];
      $title = $node->isSticky()
        ? $this->t('@title [@nid, sticky]', $args)
        : $this->t('@title [@nid]', $args);
      $matches[$title->__toString()] = $title;
    }
    $j = new CacheableJsonResponse($matches);
    foreach ($g2_match as $node) {
      $j->addCacheableDependency($node);
    }
    return $j;
  }

}
