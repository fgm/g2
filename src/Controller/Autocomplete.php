<?php

declare(strict_types = 1);

namespace Drupal\g2\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\g2\G2;
use Drupal\g2\WOTD;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   * The g2.wotd service.
   *
   * @var \Drupal\g2\WOTD
   */
  protected $wotd;

  /**
   * Autocomplete constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity.manager service.
   * @param \Drupal\g2\WOTD $wotd
   *   The g2.wotd service.
   * @param array $config
   *   The g2.settings/controller.homonyms configuration.
   */
  public function __construct(EntityTypeManagerInterface $etm, WOTD $wotd, array $config) {
    $this->config = $config;
    $this->etm = $etm;
    $this->wotd = $wotd;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get(G2::SVC_CONF);

    $config = $config_factory->get(G2::CONFIG_NAME)->get('controller.homonyms');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get(G2::SVC_ETM);

    /** @var \Drupal\g2\WOTD $wotd */
    $wotd = $container->get(G2::SVC_WOTD);

    return new static($entity_manager, $wotd, $config);
  }

  /**
   * Controller for g2.autocomplete.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Unsafe. The entry for which to find matching G2 entries.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The nodes matching the passed title portion.
   */
  public function indexAction(Request $request): CacheableJsonResponse {
    $q = $request->get('q');
    $nodes = $this->wotd->matchesFromTitle($q);
    if (empty($nodes)) {
      return new JsonResponse([]);
    }
    $matches = [];
    $tags = [];
    foreach ($nodes as $node) {
      $title = $this->wotd
        ->numberedTitleInput($node);
      $matches[] = ['value' => $title];
      $tags += $node->getCacheTags();
    }
    /** @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $ck */
    $j = new CacheableJsonResponse($matches);
    $md = (new CacheableMetadata())
      ->addCacheContexts(['url.query_args:q'])
      ->addCacheTags($tags)
      // Just a throttle for safety.
      ->setCacheMaxAge(1);
    $j->addCacheableDependency($md);
    return $j;
  }

}
