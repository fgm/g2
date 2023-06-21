<?php

namespace Drupal\g2;

use AhoCorasick\MultiStringMatcher;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * The matcher class implements the g2.matcher service.
 *
 * The mission of this service is to keep an to-date Aho-Corasick MSM of all the
 * deduplicated G2 entry titles and of the associated G2 entries, and provide
 * access to both.
 *
 * This is especially useful to ensure linear performance of the Definition
 * Process plugin.
 *
 * We keep the titles and MSM separately becase a MSM does not account for
 * duplicates, while in G2 multiple entries may share an identical title.
 *
 * See https://en.wikipedia.org/wiki/Aho%E2%80%93Corasick_algorithm
 */
class Matcher {

  /**
   * The name of the KeyValue collection used to store the FSM.
   */
  const COLLECTION = G2::NAME;

  /**
   * The maximum number of nodes to load at once when rebuilding.
   *
   * This prevents extra-long cache inserts which may fail, especially on
   * MariaDB or MySQL development instances not configured for heavy loads.
   */
  const LOAD_CHUNK = 100;

  /**
   * The keyvalue service.
   *
   * The Matcher service uses KV instead of Cache - although the data can be
   * regenerated when lost - to avoid data size issues like those with the
   * Memcache modules handling of data larger than 1MB.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected KeyValueFactoryInterface $kv;

  /**
   * A map of G2 entry IDs keys by title.
   *
   * @var array
   */
  protected array $map;

  /**
   * The MSM instance holding the titles.
   *
   * @var \AhoCorasick\MultiStringMatcher
   */
  protected MultiStringMatcher $msm;

  /**
   * The core entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreInterface $kv
   *   The keyvalue service.
   */

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The core entity_type.manager service.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $kv
   *   The core keyvalue service.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    KeyValueFactoryInterface $kv,
  ) {
    $this->etm = $etm;
    $this->kv = $kv;
  }

  /**
   * Rebuild the map and MSM from the available published G2 entries.
   *
   * Store them both in KV.
   *
   * This is a somehow costly operation, do not perform it frequently.
   * For example on a M1 Max macBook Pro, a 6000 definitions rebuild takes
   * about four seconds on a flushed cache and 0.6 sec on a primed cache.
   *
   * In most cases, you should be retrieving the map/MSM using their respective
   * get method.
   */
  public function rebuild(): void {
    $storage = $this->etm
      ->getStorage(G2::TYPE);
    $nids = $storage->getQuery()
      ->condition('type', G2::BUNDLE)
      ->condition('status', NodeInterface::PUBLISHED)
      ->accessCheck()
      ->execute();
    $chunks = array_chunk($nids, 100);
    $map = [];
    foreach ($chunks as $chunk) {
      $nodes = $storage->loadMultiple($chunk);
      foreach ($nodes as $node) {
        $map[$node->label()][] = $node->id();
      }
    }

    // Refresh map.
    $coll = $this->kv
      ->get(Matcher::COLLECTION);
    $coll->set(G2::KV_TITLES, $map);
    $this->map = $map;

    // Refresh MSM.
    $msm = new MultiStringMatcher(array_keys($map));
    $coll->set(G2::KV_MSM, $msm);
    $this->msm = $msm;
  }

  /**
   * Get the MSM.
   *
   * @return \AhoCorasick\MultiStringMatcher
   *   A ready to use matcher, found on instance or regenerated as needed.
   */
  public function getMultiStringMatcher(): MultiStringMatcher {
    // Fast path: already ready.
    if (!empty($this->msm)) {
      return $this->msm;
    }

    // Medium path: get from KV.
    $msm = $this->kv
      ->get(self::COLLECTION)
      ->get(G2::KV_MSM);
    if (!empty($msm)) {
      $this->msm = $msm;
      return $msm;
    }

    // Slow path: get from entity storage and refresh KV.
    $this->rebuild();
    return $this->msm;
  }

  /**
   * Get the map of G2 entry IDs by title.
   *
   * @return array
   *   An up-to-date map, found on instance or regenerated as needed.
   */
  public function getTitleMap(): array {
    // Fast path: already ready.
    if (!empty($this->map)) {
      return $this->map;
    }

    // Medium path: get from KV.
    $map = $this->kv
      ->get(self::COLLECTION)
      ->get(G2::KV_TITLES);
    if (!empty($map)) {
      $this->map = $map;
      return $map;
    }

    // Slow path: get from entity storage and refresh KV.
    $this->rebuild();
    return $this->map;
  }

}
