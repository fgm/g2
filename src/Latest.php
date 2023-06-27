<?php

namespace Drupal\g2;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Class Latest implements the g2.latest service.
 */
class Latest {

  use StringTranslationTrait;

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $etm
  ) {
    $this->configFactory = $configFactory;
    $this->etm = $etm;
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
    $config = $this->configFactory
      ->get(G2::CONFIG_NAME);
    $count_limit = $config->get(G2::VARLATESTMAXCOUNT);
    $count = min($count, $count_limit);

    $query = $this->etm
      ->getStorage(G2::TYPE)
      ->getQuery()
      ->accessCheck()
      ->condition('type', G2::BUNDLE)
      ->sort('changed', 'DESC')
      ->range(0, $count);
    if (!(\Drupal::currentUser()->hasPermission(G2::PERM_ADMIN))) {
      $query = $query->condition('status', NodeInterface::PUBLISHED);
    }
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
   * @return arraystring\Drupal\Core\Link
   *   A hash of nid to entry links.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getLinks($count) {
    $result = [];
    $options = [
      // So links can be used outside site pages.
      'absolute' => TRUE,
      // To preserve the pre-encoded path.
      'html' => TRUE,
    ];
    /** @var \Drupal\node\NodeInterface $node */
    foreach ($this->getEntries($count) as $node) {
      if (!$node->isPublished()) {
        $node->setTitle($this->t('@title [unpublished]', [
          '@title' => $node->label(),
        ]));
      }
      $result[] = $node->toLink(NULL, 'canonical', $options);
    }

    return $result;
  }

}
