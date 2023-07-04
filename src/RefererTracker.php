<?php

namespace Drupal\g2;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Tracks navigation to G2 entries anonymously.
 */
class RefererTracker {

  /**
   * The core database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $db;

  /**
   * The core path_alias.manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected AliasManagerInterface $pathAliasManager;

  /**
   * The core request_stack service.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Database\Connection $db
   *   The core databaser service.
   * @param \Drupal\path_alias\AliasManagerInterface $pathAliasManager
   *   The core path_alias.manager service.
   * @param \Drupal\Core\Http\RequestStack $stack
   *   The core request_stack service.
   */
  public function __construct(
    Connection $db,
    AliasManagerInterface $pathAliasManager,
    RequestStack $stack
  ) {
    $this->db = $db;
    $this->pathAliasManager = $pathAliasManager;
    $this->requestStack = $stack;
  }

  /**
   * Log a hit from a referrer.
   *
   * Note that this does not work on cached nodes, and will log hits from all
   * pages linking to a g2_entry, even if it is only from a block, not just
   * from
   * the main content.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node being viewed.
   *
   * @throws \Exception
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy
   */
  public function upsertReferer(EntityInterface $node): void {
    $req = $this->requestStack->getCurrentRequest();
    $referer = $req->headers->get('referer');
    // Ignore both missing headers and Safari >= 13.1 empty Referer headers.
    if (empty($referer)) {
      return;
    }
    $base = $req->getSchemeAndHttpHost();

    // For now, we do not handle non-local referers, which have become less
    // available over time since IE8 introduced X-Fram-Options anyway.
    if (!str_starts_with($referer, "$base/")) {
      return;
    }

    // Extract local path, possibly aliased.
    $path = mb_substr($referer, mb_strlen($base));
    // Unalias it.
    $canonical = $this->pathAliasManager->getPathByAlias($path);
    // Sanitize it.
    $escaped = Html::escape($canonical);

    // We cannot use the Upsert() method because this table has a composite PK.
    $updated = $this->db->update(G2::TBL_REFERER)
      ->condition('nid', $node->id())
      ->condition('referer', $escaped)
      ->expression('incoming', 'incoming+1')
      ->execute();
    if (!empty($updated)) {
      return;
    }
    // Update found no row to update: insert one.
    $this->db->insert(G2::TBL_REFERER)
      ->fields(['nid', 'referer', 'incoming'])
      ->values(['nid' => $node->id(), 'referer' => $escaped, 'incoming' => 1])
      ->execute();
  }

  /**
   * Erase the referer counts on g2 entries.
   *
   * @param int $nid
   *   Node from which to erase referers, or 0 to erase all g2 referers.
   */
  public function wipe(int $nid = 0) {
    $tags = ["node_list:" . G2::BUNDLE];
    if (empty($nid)) {
      $this->db
        ->truncate(G2::TBL_REFERER)
        ->execute();
      Cache::invalidateTags($tags);
      return;
    }

    $tags[] = "node:$nid";
    $this->db
      ->delete(G2::TBL_REFERER)
      ->condition('nid', $nid)
      ->execute();
    Cache::invalidateTags($tags);
  }

}
