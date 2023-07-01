<?php

declare(strict_types = 1);

namespace Drupal\g2;

use AhoCorasick\MultiStringMatcher;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * The matcher class implements the g2.matcher service.
 *
 * The mission of this service is to keep an up-to-date Aho-Corasick MSM of all
 * the deduplicated G2 entry titles and of the associated G2 entries,
 * and to provide access to both.
 *
 * This is especially useful to ensure linear performance of the G2 Automatic
 * filter plugin.
 *
 * Because the stop list may change from one format to the next, we keep the
 * complete mapping and drop entries in the stop list during content handling.
 *
 * We keep the titles and MSM separately because an MSM does not account for
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
   * Transform a single text node to insert DFN elements as needed.
   *
   * Also applies HTML normalization, e.g. closes elements that expect it.
   *
   * @param \DOMDocument $dom
   *   The document built from the text.
   * @param \DOMNode $node
   *   The text node being modified.
   * @param \AhoCorasick\MultiStringMatcher $msm
   *   The configured matcher.
   * @param array $stopList
   *   The titles in the stop list.
   *
   * @throws \DOMException
   *
   * @internal This method is only public to support unit tests.
   */
  public static function handleTextNode(
    \DOMDocument $dom,
    \DOMNode $node,
    MultiStringMatcher $msm,
    array $stopList,
  ) {
    $input = $node->nodeValue;
    $matches = $msm->searchIn($input);
    if (empty($matches)) {
      return;
    }
    $allPatterns = array_unique(array_map(fn($match) => $match[1], $matches));
    $patterns = array_diff($allPatterns, $stopList);
    if (empty($patterns)) {
      return;
    }
    $parent = $node->parentNode;
    foreach ($patterns as $pattern) {
      $rx = "/\b" . preg_quote($pattern, "/") . "\b/u";
      $res = preg_split($rx, $input, PREG_SPLIT_OFFSET_CAPTURE);
      $len = count($res);
      for ($i = 0; $i < $len; $i++) {
        $fragment = $res[$i];
        if (!empty($fragment)) {
          $parent->insertBefore($dom->createTextNode($fragment), $node);
        }
        if ($i < $len - 1) {
          $dfn = $dom->createElement('dfn', $pattern);
          $parent->insertBefore($dfn, $node);
        }
      }
    }
    $parent->removeChild($node);
  }

  /**
   * Perform the actual processing after checks.
   *
   * @param string $input
   *   The input text, assumed to be already checked for UTF-8 validity.
   * @param \AhoCorasick\MultiStringMatcher $msm
   *   The MSM.
   * @param array $stopList
   *   The titles of the node not to automatically wrap in a DFN.
   *
   * @return string
   *   The processed source.
   *
   * @internal This method is only public to support unit tests.
   */
  public static function handleSource(
    string $input,
    MultiStringMatcher $msm,
    array $stopList
  ): string {
    if (empty($input)) {
      return "";
    }
    $dom = Html::load($input);
    $xp = new \DOMXPath($dom);
    // We do not want to parse "script" or "style" content, and we also want to
    // avoid existing "dfn" and "a" content because in the end the "dfn"
    // elements will be converted to "a href" and those cannot be nested.
    $texts = $xp->query('//text()[not(ancestor::script) and not(ancestor::style) and not(ancestor::dfn) and not(ancestor::a)]');
    /** @var \DOMNode $text */
    foreach ($texts as $text) {
      self::handleTextNode($dom, $text, $msm, $stopList);
    }
    $bodyTags = $dom->getElementsByTagName('body');
    assert(count($bodyTags) === 1);
    $body = $bodyTags->item(0);
    // There may be multiple elements at the top if there is no top wrapper.
    assert($body->childNodes->count() >= 1);
    $output = '';
    foreach ($body->childNodes as $node) {
      $output .= $dom->saveHTML($node);
    }
    return $output;
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
        $label = $node->label();
        $map[$label][] = $node->id();
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
