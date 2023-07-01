<?php

declare(strict_types = 1);

namespace Drupal\g2;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\g2\Exception\RandomException;
use Drupal\node\NodeInterface;

/**
 * Class Random provides data for the Random block and API.
 */
class Random {

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $config;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $db;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Random constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.factory service.
   * @param \Drupal\Core\Database\Connection $db
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager server.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(
    ConfigFactoryInterface $config,
    Connection $db,
    EntityTypeManagerInterface $etm,
    StateInterface $state,
  ) {
    $this->config = $config;
    $this->db = $db;
    $this->etm = $etm;
    $this->state = $state;
  }

  /**
   * Get the list of current nodes to avoid, by nid or title.
   *
   * @return array
   *   - title: to be avoided
   *   - nid: to be avoided
   */
  protected function getAvoidedEntries(ImmutableConfig $conf): array {
    $avoided = [];

    // Avoid the stored random if set and any entry with the same title.
    if ($conf->get(G2::VARRANDOMSTORE)) {
      $randomTitle = $this->state->get(G2::VARRANDOMENTRY, G2::DEFRANDOMENTRY);
      if (!empty($randomTitle)) {
        $avoided['title'] = $randomTitle;
      }
    }

    // Avoid the stored WOTD.
    $wotdNid = (int) $conf->get(G2::VARWOTDENTRY);
    if ($wotdNid > 0) {
      $avoided['nid'] = $wotdNid;
    }

    return $avoided;
  }

  /**
   * Return a pseudo-random G2 Entry.
   *
   * Entry is selected to be different from the current WOTD and, in the default
   * setting, from the latest pseudo-random result returned.
   *
   * Only works for glossaries with 3 entries or more.
   *
   * This uses plain DB instead of entityQuery, because on D9.5.9, Apple M1 Max,
   * PHP 8.1, the former is faster than the latter by a factor of up to 6:
   * 0.2-0.4 msec vs 1.2-2 msec.
   *
   * @return \Drupal\node\NodeInterface
   *   The chosen random node.
   *
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   */
  public function get(): NodeInterface {
    $conf = $this->config->get(G2::CONFIG_NAME);
    $avoided = $this->getAvoidedEntries($conf);
    $randomTitle = $avoided['title'] ?? '';
    $wotdNid = $avoided['nid'] ?? 0;

    $this->db->startTransaction();
    $q = $this->db
      ->select('node_field_data', 'nfd')
      ->condition('nfd.type', G2::BUNDLE)
      ->condition('nfd.status', NodeInterface::PUBLISHED)
      ->addTag('node_access');
    if ($randomTitle) {
      $q = $q->condition('nfd.title', $randomTitle, '<>');
    }
    if ($wotdNid) {
      $q = $q->condition('nfd.nid', $avoided['nid'], '<>');
    }
    $n = (int) $q->countQuery()
      ->execute()
      ->fetchField();
    if ($n === 0) {
      throw new RandomException("No entry outside WOTD and stored random.");
    }

    // No longer need to mt_srand() since PHP 4.2.
    $randIndex = mt_rand(0, $n - 1);

    // Select from the exact same list of nodes, the transaction guaranteeing
    // that none was visibly inserted/deleted in the meantime.
    $q = $this->db
      ->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.type', G2::BUNDLE)
      ->condition('nfd.status', NodeInterface::PUBLISHED)
      ->range($randIndex, 1)
      ->addTag('node_access');
    if ($randomTitle) {
      $q = $q->condition('nfd.title', $avoided['title'], '<>');
    }
    if ($wotdNid) {
      $q = $q->condition('nfd.nid', $avoided['nid'], '<>');
    }
    $nid = (int) $q->execute()
      ->fetchField();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->etm
      ->getStorage(G2::TYPE)
      ->load($nid);
    if (empty($node)) {
      throw new RandomException("Found no random node, but expected to find one.");
    }

    if ($conf->get(G2::VARRANDOMSTORE)) {
      // Unfiltered.
      $this->state->set(G2::VARRANDOMENTRY, $node->label());
    }

    return $node;
  }

}
