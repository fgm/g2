<?php

namespace Drupal\g2;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGenerator;

/**
 * Class Latest implements the g2.latest service.
 */
class Latest {
  /**
   * The configuration hash for this service.
   *
   * Keys:
   * - max: the maximum number of entries returned. 0 for unlimited.
   *
   * @var array
   */
  protected $config;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGenerator
   */
  protected $linkGenerator;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param \Drupal\Core\Utility\LinkGenerator $link_generator
   *   The link generator service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config,
    LinkGenerator $link_generator,
    UrlGeneratorInterface $url_generator,
    EntityTypeManagerInterface $etm
  ) {
    $this->etm = $etm;
    $this->linkGenerator = $link_generator;
    $this->urlGenerator = $url_generator;

    $g2_config = $config->get(G2::CONFIG_NAME);
    $this->config = $g2_config->get('services.latest');
  }

  /**
   * Return the latest updated entries.
   *
   * @param int $count
   *   The maximum number of entries to return. Limited both by the configured
   *   maximum number of entries and the actual number of entries available.
   *
   * @return arrayinteger\Drupal\node\NodeInterface
   *   A node-by-nid hash, ordered by latest change timestamp.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntries($count) {
    $count_limit = $this->config['max_count'];
    $count = min($count, $count_limit);

    $query = $this->etm
      ->getStorage(G2::TYPE)
      ->getQuery()
      ->condition('type', G2::BUNDLE)
      ->sort('changed', 'DESC')
      ->range(0, $count);
    $ids = $query->execute();
    $result = $this->etm
      ->getStorage(G2::TYPE)
      ->loadMultiple($ids);
    return $result;
  }

  /**
   * Return an array of links to entry pages.
   *
   * @param int $count
   *   The maximum number of entries to return. Limited both by the configured
   *   maximum number of entries and the actual number of entries available.
   *
   * @return array<string\Drupal\Core\GeneratedLink>
   *   A hash of nid to to entry links.
   */
  public function getLinks($count) {
    $result = [];
    $options = [
      // So links can be used outside site pages.
      'absolute' => TRUE,
      // To preserve the pre-encoded path.
      'html'     => TRUE,
    ];
    $route_name = 'entity.node.canonical';

    /** @var \Drupal\node\NodeInterface $node */
    foreach ($this->getEntries($count) as $node) {
      $parameters = [G2::TYPE => $node->id()];
      $url = Url::fromRoute($route_name, $parameters, $options);
      $result[] = $this->linkGenerator->generate($node->label(), $url);
    }

    return $result;
  }

}
