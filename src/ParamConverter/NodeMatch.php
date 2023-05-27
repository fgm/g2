<?php

namespace Drupal\g2\ParamConverter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\g2\G2;
use Drupal\node\Entity\Node;
use Symfony\Component\Routing\Route;

/**
 * Class NodeMatch is a flexible ParamConverter.
 *
 * Depending on its use configuration, it will match:
 * - matches at beginning, matches anywhere in title, or only full matches.
 * - returning a configurable number of results.
 */
class NodeMatch implements ParamConverterInterface {

  /**
   * The current_user service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $etm;

  /**
   * NodeMatch constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current_user service.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    AccountProxy $current_user,
  ) {
    $this->currentUser = $current_user;
    $this->etm = $etm;
  }

  /**
   * {@inheritdoc}
   *
   * Only returns unpublished nodes to users with "administer g2 entries".
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|mixed|null
   *   A possibly empty array of nodes.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function convert($value, $definition, $name, array $defaults) {
    // XXX earlier versions used "administer nodes". Which one is better ?
    $min_status = $this->currentUser->hasPermission(G2::PERM_ADMIN)
      ? Node::NOT_PUBLISHED
      : Node::PUBLISHED;

    $query = $this->etm
      ->getStorage('node')
      ->getQuery()
      ->addTag('node_access')
      ->condition('type', G2::NODE_TYPE)
      ->condition('status', $min_status, '>=')
      ->condition('title', $value . '%', 'LIKE');

    $ids = $query->execute();
    $nodes = $this->etm->getStorage('node')->loadMultiple($ids);

    return $nodes;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    $result = !empty($definition['type']) && $definition['type'] == 'g2:node:title';
    return $result;
  }

}
