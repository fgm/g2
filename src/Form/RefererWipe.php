<?php

declare(strict_types = 1);

namespace Drupal\g2\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\g2\RefererTracker;
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
   * The URL to return to when canceling.
   *
   * @var \Drupal\Core\Url
   */
  protected Url $cancelUrl;

  /**
   * The g2.referer_tracker service.
   *
   * @var \Drupal\g2\RefererTracker
   */
  protected RefererTracker $tracker;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $db
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   * @param \Drupal\g2\RefererTracker $tracker
   *   The g2.referer_tracker service.
   */
  public function __construct(
    Connection $db,
    EntityTypeManagerInterface $etm,
    RefererTracker $tracker,
  ) {
    $this->db = $db;
    $this->etm = $etm;
    $this->tracker = $tracker;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $db = $container->get('database');
    $etm = $container->get(G2::SVC_ETM);
    $tracker = $container->get(G2::SVC_TRACKER);
    return new static($db, $etm, $tracker);
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
    return $form;
  }

  /**
   * Form builder for the "wipe one" case.
   *
   * @param array $form
   *   The initial form.
   * @param \Drupal\node\NodeInterface $node
   *   The node whose referers to wipe.
   *
   * @return array
   *   The enriched form.
   */
  public function wipeOne(array $form, NodeInterface $node) {
    $nid = (int) $node->id();
    $form[G2::TYPE] = [
      '#markup' => '<p>'
      . $this->t('Wipe reference on single node "@title" (@nid) ?', [
        '@title' => $node->label(),
        '@nid' => $nid,
      ])
      . '</p>',
    ];
    $form['nid'] = [
      '#type' => 'value',
      '#value' => $nid,
    ];
    $this->cancelUrl = Url::fromRoute(G2::ROUTE_NODE_CANONICAL, [
      'node' => $nid,
    ]);
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RouteMatchInterface $route = NULL): array {
    switch ($route->getRouteName()) {
      case G2::ROUTE_WIPE_ALL:
        $form = $this->wipeAll($form);
        break;

      case G2::ROUTE_WIPE_ONE:
        $node = $route->getParameter('node');
        $form = $this->wipeOne($form, $node);
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
  public function submitForm(array &$form, FormStateInterface $formState) {
    $nid = (int) $formState->getValue('nid');
    $this->tracker->wipe($nid);

    $status = empty($nid)
      ? $this->t("All referers wiped out")
      : $this->t('Referers wiped out on node @nid', ['@nid' => $nid]);
    $this->messenger()->addStatus($status);

    $formState->setRedirect((empty($nid)
      ? G2::ROUTE_CONFIG_CONTROLLERS
      : G2::ROUTE_NODE_CANONICAL),
    ['node' => $nid]);
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
    $d = new TranslatableMarkup("Do you really want to erase HTTP referers? This action cannot be undone.");
    return $d;
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Wipe referers');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    if (empty($this->cancelUrl)) {
      return Url::fromRoute(G2::ROUTE_CONFIG_CONTROLLERS);
    }

    return $this->cancelUrl;
  }

}
