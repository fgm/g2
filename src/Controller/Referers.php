<?php

namespace Drupal\g2\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for G2 glossary routes.
 */
class Referers extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $db;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    Connection $connection,
  ) {
    $this->etm = $etm;
    $this->db = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

  /**
   * Counts the number of G2 referer entries.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to examine.
   *
   * @return string
   *   HTML
   *
   * @todo check referer wipe: it may have been damaged in the D6 port
   */
  public function countReferers(NodeInterface $node) {
    // Build list of referers.
    $nid = (int) $node->id();

    $header = [
      ['data' => $this->t('Clicks'), 'field' => 'incoming', 'sort' => 'desc'],
      ['data' => $this->t('Referer'), 'field' => 'referer', 'sort' => 'asc'],
      ['data' => $this->t('Related node')],
    ];

    // Can be generated for unpublished nodes by author or admin, so don't
    // filter on node.status = 1
    // The join is needed to avoid showing info about forbidden nodes, and
    // to allow some modules to interfere without breaking because they
    // assume "nid" only exists in {node}.
    $q = $this->db
      ->select(G2::TBL_REFERER, 'g2r')
      ->extend(TableSortExtender::class);
    $q->innerJoin('node', 'n', 'g2r.nid = n.nid');
    $q->fields('g2r', ['referer', 'incoming'])
      ->condition('g2r.nid', $nid)
      ->addTag('node_access')
      ->orderByHeader($header);

    $res = $q->execute();
    $rows = [];
    foreach ($res as $row) {
      $sts = preg_match('/node\/(\d+)/', $row->referer, $matches);
      if ($sts) {
        $node = $this->etm
          ->getStorage(G2::TYPE)
          ->load($matches[1]);
        $title = Link::createFromRoute(
          $node->label(),
          G2::ROUTE_NODE_CANONICAL,
          ['node' => $node->id()],
        )->toString();
      }
      else {
        $title = NULL;
      }
      $rows[] = empty($row->referer)
        // Should never happen.
        ? [$row->incoming, $this->t('<empty>'), $title]
        : [
          $row->incoming,
          Link::fromTextAndUrl($row->referer, Url::fromUserInput($row->referer)),
          $title,
        ];
    }

    $build = [];

    $build['links'] = [
      '#prefix' => $this->t('<h3>Local referers for this node</h3>'),
    ];
    if (empty($rows)) {
      $build['links']['#type'] = 'markup';
      $build['links']['#markup'] = '<p>' . $this->t('No referer found. Maybe you just cleaned the list ?') . '</p>';
    }
    else {
      $build['links']['#type'] = 'table';
      $build['links']['#header'] = $header;
      $build['links']['#rows'] = $rows;
    }

    if (!empty($rows)) {
      // @todo explain what these stats really measure.
      $build['links']['#suffix'] = $this->t(
        "<p>WARNING: these stats are just a hint for editors.</p>
<ul>
  <li>Just because a click came from a node doesn't mean the node has a link.
    The click may have come from a block on the page.</li>
  <li>These reference counts are not page views:
    they count views in all view displays, not just full page,
    and they miss cached views, e.g. authenticated users. </li>
</ul>");
    }

    $build['wipe'] = [
      '#markup' => '<p>'
      . $this->t('<a href=":wipeone">Wipe referers on this node</a>', [
        ':wipeone' => Url::fromRoute(G2::ROUTE_WIPE_ONE, [
          'node' => $node->id(),
        ])->toString(),
      ])
      . "</p>",
    ];

    $build['#cache'] = [
      'tags' => ['node_list:' . G2::BUNDLE, "node:$nid"],
      'max-age' => 1,
    ];
    return $build;
  }

}
