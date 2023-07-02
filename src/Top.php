<?php

declare(strict_types = 1);

namespace Drupal\g2;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Utility\LinkGenerator;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Top implements the g2.top service.
 */
class Top {
  const STATISTICS_DAY = 'daycount';
  const STATISTICS_TOTAL = 'totalcount';

  const STATISTICS_TYPES = [self::STATISTICS_DAY, self::STATISTICS_TOTAL];

  /**
   * The service availability status.
   *
   * @var bool
   */
  protected $available;

  /**
   * The core config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGenerator
   */
  protected LinkGenerator $linkGenerator;

  /**
   * The logger.channel.g2 service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected UrlGeneratorInterface $urlGenerator;

  /**
   * The core entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The core config.factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The core entity_type.manager service.
   * @param \Drupal\Core\Utility\LinkGenerator $link_generator
   *   The link generator service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module_handler service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.channel.g2 service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $etm,
    LinkGenerator $link_generator,
    UrlGeneratorInterface $url_generator,
    ModuleHandlerInterface $module_handler,
    Connection $connection,
    LoggerInterface $logger
  ) {
    $this->available = $module_handler->moduleExists('statistics');
    $this->configFactory = $configFactory;
    $this->database = $connection;
    $this->etm = $etm;
    $this->linkGenerator = $link_generator;
    $this->logger = $logger;
    $this->urlGenerator = $url_generator;
  }

  /**
   * Return the top visited entries.
   *
   * @param int $count
   *   The maximum number of entries to return. Limited both by the configured
   *   maximum number of entries and the actual number of entries available.
   * @param string $statistic
   *   The type of statistic by which to order. Must be one of the
   *   self::STATISTICS_* individual statistics.
   *
   * @return \Drupal\node\NodeInterface[]
   *   A node-by-nid hash, ordered by latest change timestamp.
   */
  public function getNodes(int $count, string $statistic = self::STATISTICS_DAY): array {
    if (!$this->available) {
      return [];
    }
    if (!in_array($statistic, static::STATISTICS_TYPES)) {
      return [];
    }
    $maxCount = $this->configFactory
      ->get(G2::CONFIG_NAME)
      ->get(G2::VARTOPMAXCOUNT);
    $count = min($count, $maxCount);

    $query = $this->database
      ->select('node_counter', 'nc')
      ->fields('nc', ['nid'])
      ->addTag('node_access')
      ->condition('nfd.status', NodeInterface::PUBLISHED)
      ->condition('nfd.type', G2::BUNDLE)
      ->condition("nc.${statistic}", 0, '<>')
      ->orderBy("nc.${statistic}", 'DESC')
      ->range(0, $count);
    $query->innerJoin('node_field_data', 'nfd', 'nfd.nid = nc.nid');
    $executed = $query->execute();
    if (empty($executed)) {
      return [];
    }
    $nids = $executed->fetchCol(0);
    $nodes = $this->etm
      ->getStorage(G2::TYPE)
      ->loadMultiple($nids);
    return $nodes;
  }

  /**
   * Return an array of links to entry pages.
   *
   * @param int $count
   *   The maximum number of entries to return. Limited both by the configured
   *   maximum number of entries and the actual number of entries available.
   * @param string $statistic
   *   The type of statistic by which to order. Must be one of the
   *   self::STATISTICS_* individual statistics.
   *
   * @return arraystring\Drupal\Core\GeneratedLink
   *   A hash of nid to entry links.
   */
  public function getLinks(int $count, string $statistic = self::STATISTICS_DAY): array {
    $result = [];
    if (!$this->available) {
      return $result;
    }

    $options = [
      // So links can be used outside site pages.
      'absolute' => TRUE,
      // To preserve the pre-encoded path.
      'html' => TRUE,
    ];

    foreach ($this->getNodes($count, $statistic) as $node) {
      $url = $node->toUrl('canonical', $options);
      $result[] = $this->linkGenerator->generate($node->label(), $url);
    }

    return $result;
  }

  /**
   * Is the service available ?
   *
   * @return bool
   *   The service availability status.
   */
  public function isAvailable() {
    return $this->available;
  }

}
