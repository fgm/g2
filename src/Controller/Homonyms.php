<?php

declare(strict_types=1);

namespace Drupal\g2\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\node\NodeInterface;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Homonyms contains the controller for the entry list pages.
 *
 * Pages are:
 * - Entries by full name.
 * - Entries by initial.
 */
class Homonyms implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Title of the G2 by-initial pages.
   */
  const ENTRIES_BY_INITIAL = 'G2 entries starting with initial %initial';

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * Homonyms constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity_type.manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The g2.settings/controller.homonyms configuration.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, ConfigFactoryInterface $configFactory) {
    $this->etm = $entity_manager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $container->get(G2::SVC_CONF);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $etm */
    $etm = $container->get(G2::SVC_ETM);
    return new static($etm, $configFactory);
  }

  /**
   * Build a "no match" themed response.
   *
   * @param string $raw_match
   *   The raw, unsafe string requested.
   *
   * @return arraystringarray|string
   *   A render array.
   *
   * @FIXME passing "+" (unquoted) causes notice in getAliasByPath().
   */
  protected function indexNoMatch($raw_match) {
    $message = $this->t('There are currently no entries for %entry.', ['%entry' => $raw_match]);

    $may_create = $this->etm
      ->getAccessControlHandler(G2::TYPE)
      ->createAccess(G2::BUNDLE);
    if ($may_create) {
      $arguments = [
        'node_type' => G2::BUNDLE,
      ];
      $options = [
        'query' => ['title' => urlencode($raw_match)],
      ];
      $offer = $this->t('Would you like to <a href=":url" title="Create new entry for @entry">create one</a> ?', [
        ':url' => Url::fromRoute('node.add', $arguments, $options)->toString(),
        '@entry' => $raw_match,
      ]);
    }
    else {
      $offer = NULL;
    }

    $result = [
      '#theme' => 'g2_entries',
      '#offer' => $offer,
      '#message' => $message,
    ];

    return $result;
  }

  /**
   * Build a redirect response to the matching G2 entry canonical URL.
   *
   * @param \Drupal\node\NodeInterface[] $g2_match
   *   The match array, containing a single node entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   */
  protected function indexRedirectSingleMatch(array $g2_match) {
    $status = $this->configFactory
      ->get(G2::CONFIG_NAME)
      ->get(G2::VARHOMONYMSREDIRECTSTATUS);
    assert('is_int($status) && ($status === 201 || $status >= 300 && $status <= 399)', 'redirect is a redirect');
    assert(count($g2_match) !== 0);
    /** @var \Drupal\Core\Entity\EntityInterface $node */
    $node = reset($g2_match);
    $redirect = $node->toUrl()->toString();
    $response = new RedirectResponse($redirect, $status);
    return $response;
  }

  /**
   * Build the generic multi-match themed response.
   *
   * @param string $raw_match
   *   The raw, unsafe string requested.
   * @param \Drupal\node\NodeInterface[] $g2_match
   *   The match array, containing node entities indexed by nid.
   *
   * @return arraystringarray|string
   *   A render array.
   */
  protected function indexMatches($raw_match, array $g2_match) {
    $entries = $this->etm
      ->getViewBuilder(G2::TYPE)
      ->viewMultiple($g2_match, G2::VM_ENTRY_LIST);
    $result = [
      '#theme' => 'g2_entries',
      '#raw_entry' => $raw_match,
      '#entries' => $entries,
    ];
    return $result;
  }

  /**
   * Build a homonyms page using a node instead of the match information.
   *
   * This is an old feature, included for compatibility with antique versions,
   * but it is better to avoid it and use a custom route instead, which will be
   * able to take advantage of the converted parameters and have versions code.
   *
   * @param int $nid
   *   The node to use to build the page.
   *
   * @return arraystringarray|string
   *   A render array.
   *
   * @deprecated in drupal:8.1.0 and is removed from drupal:8.2.0. Use a view.
   * @see https://www.drupal.org/project/g2/issues/3369887
   */
  protected function indexUsingNode($nid) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->etm
      ->getStorage(G2::TYPE)
      ->load($nid);
    $builder = $this->etm
      ->getViewBuilder(G2::TYPE);
    $result = $builder->view($node, G2::VM_HOMONYMS_PAGE);
    return $result;
  }

  /**
   * Build a homonyms page using a view instead of the match information.
   *
   * View is invoked using the unsafe raw_match.
   *
   * @param string $raw_match
   *   The raw, unsafe string requested.
   * @param string $view_id
   *   The id of the view to use.
   *
   * @return arraystringarray|string
   *   A render array.
   */
  protected function indexUsingView(string $raw_match, string $view_id) {
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = $this->etm->getStorage('view')->load($view_id);
    assert($view instanceof ViewEntityInterface);

    $executable = $view->getExecutable();
    assert($executable instanceof ViewExecutable);

    $result = $executable->access('default')
      ? $executable->preview('default', [$raw_match])
      : [];

    return $result;
  }

  /**
   * Controller for g2.homonyms.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The current route.
   * @param \Drupal\node\NodeInterface[] $g2_match
   *   Unsafe. The entry for which to find matching G2 entries.
   *
   * @return arraystringarray|string|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Render array or redirect response.
   */
  public function indexAction(RouteMatchInterface $route, array $g2_match) {
    $raw_match = $route->getRawParameter('g2_match');

    if (empty($g2_match)) {
      return $this->indexNoMatch($raw_match);
    }

    $settings = $this->configFactory->get(G2::CONFIG_NAME);

    $matches = array_filter($g2_match, fn(NodeInterface $node) => $node->label() === $raw_match);
    switch (count($matches)) {
      /* @noinspection PhpMissingBreakStatementInspection */
      case 1:
        $redirect = $settings->get(G2::VARHOMONYMSREDIRECTSINGLE);
        if ($redirect) {
          $result = $this->indexRedirectSingleMatch($matches);
          break;
        }
        /* Single match handled as any other non-0 number, so fall through. */

      default:
        $nid = $settings->get(G2::VARHOMONYMSNID);
        $vid = $settings->get(G2::VARHOMONYMSVID);
        if (!empty($nid)) {
          $result = $this->indexUsingNode($nid);
        }
        elseif (!empty($vid)) {
          $result = $this->indexUsingView($raw_match, $vid);
        }
        else {
          $result = $this->indexMatches($raw_match, $matches);
        }
        break;
    }
    if (!isset($result)) {
      $result = ['#plain_text' => $this->t('No matching entry found.')];
    }
    return $result;
  }

  /**
   * Title callback for g2.homonyms.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The current route match.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function indexTitle(RouteMatchInterface $route) {
    $raw_match = $route->getRawParameter('g2_match');
    return $this->t('%entry may refer to:', ['%entry' => $raw_match]);
  }

}
