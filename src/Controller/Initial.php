<?php

declare(strict_types = 1);

namespace Drupal\g2\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\g2\G2;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Initial contains the controller for the items-by-initial page.
 */
class Initial implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * Initial constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current_user service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   */
  public function __construct(
    Connection $database,
    AccountInterface $current_user,
    EntityTypeManagerInterface $etm,
  ) {
    $this->currentUser = $current_user;
    $this->database = $database;
    $this->etm = $etm;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Session\AccountInterface $current_user */
    $current_user = $container->get('current_user');
    /** @var \Drupal\Core\Database\Connection $database */
    $database = $container->get('database');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $etm */
    $etm = $container->get(G2::SVC_ETM);
    return new static($database, $current_user, $etm);
  }

  /**
   * Return a list of words starting with an initial segment.
   *
   * Segments are typically one letter, but can be any starting substring.
   *
   * The logic is different from the one in G2\entries() because we don't care
   * for the special case of "/" as an initial segment.
   *
   * XXX Abstract to EntityQuery.
   *
   * @param string $initial
   *   Usually a single letter. Assumed to be safe, so do not call this method
   *   on raw user input.
   *
   * @return arraystringarray
   *   A render array.
   */
  protected function getByInitial($initial) {
    $ar_total = $this->getStats();
    $ar_initial = $this->getStats(0, $initial);

    $stats_basic = $this->t("<p>Displaying @count entries starting with '%initial' from a total number of @total entries.</p>",
      [
        // _g2_stats() does not return empty arrays, so no need to check values.
        '@count' => $ar_initial[Node::PUBLISHED],
        '%initial' => $initial,
        '@total' => $ar_total[Node::PUBLISHED],
      ]
    );

    if ($this->currentUser->hasPermission(G2::PERM_ADMIN)) {
      $stats_admin = $this->t('<p>Admin info: there are also @count unpublished matching entries from a total number of @total unpublished entries.</p>',
        [
          '@count' => $ar_initial[Node::NOT_PUBLISHED],
          '@total' => $ar_total[Node::NOT_PUBLISHED],
        ]
      );
    }
    else {
      $stats_admin = NULL;
    }

    unset($ar_initial);
    unset($ar_total);

    $query = $this->database->select(G2::TYPE, 'n');
    $query->innerJoin('node_field_revision', 'nfv', 'n.vid = nfv.vid');
    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = $query->fields('n', ['nid'])
      ->orderBy('nfv.title')
      ->addTag('node_access');
    $query
      ->condition('n.type', G2::BUNDLE)
      ->condition('nfv.status', 1)
      ->condition('nfv.title', $initial . '%', 'LIKE');

    $node_ids = [];
    $result = $query->execute();
    foreach ($result as $row) {
      $node_ids[] = $row->nid;
    }

    $nodes = $this->etm
      ->getStorage(G2::TYPE)
      ->loadMultiple($node_ids);
    if (empty($nodes)) {
      $result = [
        'entries' => ['#markup' => $this->t('No entry found for %initial.', ['%initial' => $initial])],
      ];
    }
    else {
      $builder = $this->etm->getViewBuilder(G2::TYPE);
      $result = [
        'stats-basic' => ['#markup' => $stats_basic],
        'entries' => $builder->viewMultiple($nodes, G2::VM_ENTRY_LIST),
      ];
    }

    $result['stats-admin'] = ['#markup' => $stats_admin];
    $result['entries']['#weight'] = 10;

    return $result;
  }

  /**
   * Controller for route g2.initial.
   *
   * @param string $g2_initial
   *   The raw initial matching the route regexp.
   *
   * @return arraystringstring|arraystringarray
   *   The render array.
   */
  public function indexAction($g2_initial) {
    // Parameter g2_initial has been checked against route regex, so it is safe.
    $nodes = $this->getByInitial($g2_initial);
    return [
      '#theme' => 'g2_initial',
      '#entries' => $nodes,
      '#initial' => $g2_initial,
    ];
  }

  /**
   * Title callback for route g2.initial.
   *
   * @param string $g2_initial
   *   The raw initial matching the route regexp.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function indexTitle($g2_initial = '@') {
    $result = $this->t('Entries starting with initial %initial', [
      '%initial' => $g2_initial,
    ]);

    return $result;
  }

  /**
   * Extract statistics from the G2 glossary.
   *
   * @param int $tid
   *   Taxonomy term id.
   * @param null|string $initial
   *   Initial segment.
   *
   * @return arrayintegerinteger
   *   - 0: g2 entries having chosen taxonomy term
   *   - 1: g2 entries starting with chosen initial segment
   */
  protected function getStats($tid = 0, $initial = NULL) {
    $sql = <<<SQL
SELECT
  COUNT(distinct n.nid) cnt, nfd.status
FROM {node} n
  INNER JOIN {node_field_revision} nfd ON n.vid = nfd.vid

SQL;

    $sq_params = [];
    $sq_test = "WHERE n.type = :node_type \n";

    $sq_params[':node_type'] = G2::BUNDLE;

    if (isset($tid) && is_int($tid) && $tid > 0) {
      $sql .= "  INNER JOIN {taxonomy_index} tn ON n.nid = tn.nid \n";
      $sq_test .= "  AND tn.tid = :tid \n";
      $sq_params[':tid'] = $tid;
    }

    if (isset($initial) && !empty($initial)) {
      $sq_test .= "  AND nfd.title LIKE :title \n";
      $sq_params[':title'] = $initial . '%';
    }
    $sql .= $sq_test . " GROUP BY nfd.status \n";

    $counts = $this->database->query($sql, $sq_params);

    // Avoid empty returns.
    $result = [
      Node::NOT_PUBLISHED => 0,
      Node::PUBLISHED => 0,
    ];

    foreach ($counts as $row) {
      $result[intval($row->status)] = intval($row->cnt);
    }

    return $result;
  }

}
