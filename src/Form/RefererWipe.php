<?php

namespace Drupal\g2\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RefererWipe contains the referer wipe confirmation form.
 *
 * It offers to clear referers for all entries or one entry.
 */
class RefererWipe extends ConfirmFormBase {

  /**
   * Title of the referer wipeout page, and associated submit button.
   */
  const TITLE = 'Wipe all G2 referer information';

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
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $db
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   */
  public function __construct(
    Connection $db,
    EntityTypeManagerInterface $etm,
  ) {
    $this->db = $db;
    $this->etm = $etm;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $db = $container->get('database');
    $etm = $container->get(G2::SVC_ETM);
    return new static($db, $etm);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $match = $this->getRouteMatch();
    $variant = $match->getParameter('variant');
    $result = 'g2_wipe-' . $variant;
    return $result;
  }

  /**
   * Form builder for the "wipe all" case.
   *
   * @param array $form
   *   The initial form.
   *
   * @return array
   *   The enriched form.
   */
  public function wipeAll(array $form) {
    // ["g2_referer_wipe_confirm_form"]
    return $form;
  }

  /**
   * Form builder for the "wipe one" case.
   *
   * @param array $form
   *   The initial form.
   *
   * @return array
   *   The enriched form.
   */
  public function wipeOne(array $form) {
    // ['G2::referer_wipe_confirm_form', 2]
    $form[G2::TYPE] = [
      '#markup' => '<p>'
      . $this->t('Wipe reference on single node')
      . '</p>',
    ];
    return $form;
  }

  /**
   * Counts the number of G2 referer entries.
   *
   * @param array $form
   *   The initial form, containing the form_id attribute.
   * @param \Drupal\node\NodeInterface $node
   *   The node to examine.
   *
   * @return string
   *   HTML
   *
   * @todo check referer wipe: it may have been damaged in the D6 port
   */
  public function countReferrers(array $form, NodeInterface $node) {
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
      ->select('g2_referer', 'g2r')
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

    // Build form from results.
    $form['links'] = [
      '#prefix' => $this->t('<h3>Local referers for this node</h3>'),
    ];
    if (empty($rows)) {
      $form['links']['#type'] = 'markup';
      $form['links']['#markup'] = '<p>' . $this->t('No referer found. Maybe you just cleaned the list ?') . '</p>';
    }
    else {
      $form['links']['#type'] = 'table';
      $form['links']['#header'] = $header;
      $form['links']['#rows'] = $rows;
    }

    if (!empty($rows)) {
      // @todo explain what these stats really measure.
      $form['links']['#suffix'] = $this->t(
        "<p>WARNING: just because a click came from a node doesn't mean the node has a link.
    The click may have come from a block on the page. These stats are just a hint for editors.</p>"
      );

      $form['wipe_target'] = [
        '#type' => 'value',
        '#value' => $nid,
      ];
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RouteMatchInterface $route = NULL): array {
    switch ($route->getRouteName()) {
      case G2::ROUTE_REFERRERS:
        $node = $route->getParameter(G2::TYPE);
        $form = $this->countReferrers($form, $node);
        break;

      case G2::ROUTE_WIPE_ALL:
        $form = $this->wipeAll($form);
        break;

      case G2::ROUTE_WIPE_ONE:
        $form = $this->wipeOne($form);
        break;

      default:
        throw new \DomainException("Unknown route for the RefererWipe form");
    }

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @fixme Implement submitForm() method.
    $this->messenger()->addStatus("Referers wiped out");
    $form_state->setRedirect(G2::ROUTE_MAIN);
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelText(): TranslatableMarkup {
    // @todo Implement method.
    $pt = new TranslatableMarkup("Cancel erasure");
    return $pt;
  }

  /**
   * {@inheritDoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    // @todo Implement method.
    $ct = new TranslatableMarkup("Confirm erasure");
    return $ct;
  }

  /**
   * {@inheritDoc}
   */
  public function getDescription(): TranslatableMarkup {
    // @todo Implement method.
    $d = parent::getDescription();
    $d = new TranslatableMarkup("Do you really want to erase referrers? This action cannot be undone.");
    return $d;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): TranslatableMarkup {
    // @todo Implement getQuestion() method.
    return $this->t('Question?');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute(G2::ROUTE_MAIN);
  }

}
