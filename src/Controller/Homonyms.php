<?php

/**
 * @file
 * Contains G2 homonyms controller.
 */

namespace Drupal\g2\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\node\Entity\Node;
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
  const CONFIG_REDIRECT_SINGLE = 'redirect_on_single_match';

  const CONFIG_REDIRECT_STATUS = 'redirect_status';

  /**
   * Title of the G2 by-initial pages.
   */
  const ENTRIES_BY_INITIAL = 'G2 entries starting with initial %initial';

  const VIEW_MODE = 'g2_entry_list';

  /**
   * The g2.settings configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * The entity.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Homonyms constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity.manager service.
   * @param array $config
   *   The g2.settings/controller.homonyms configuration.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, array $config) {
    $this->entityManager = $entity_manager;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Config\ConfigFactoryInterface  $config_factory */
    $config_factory = $container->get('config.factory');

    $config = $config_factory->get(G2::CONFIG_NAME)->get('controller.homonyms');

    /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');
    return new static($entity_manager, $config);
  }

  /**
   * Build a "no match" themed response.
   *
   * @param string $raw_match
   *   The raw, unsafe string requested.
   *
   * @return array<string,array|string>
   *   A render array.
   *
   * @TODO find a way to add the title to the node.add route for ease of creation.
   */
  protected function indexNoMatch($raw_match) {
    $message = t('There are currently no entries for %entry.', ['%entry' => $raw_match]);

    $may_create = $this->entityManager->getAccessControlHandler('node')->createAccess(G2::NODE_TYPE);
    if ($may_create) {
      $arguments = [
        'node_type' => G2::NODE_TYPE,
      ];
      $options = [
        'query' => ['title' => $raw_match],
      ];
      $offer = t('Would you like to <a href=":url" title="Create new entry for @entry">create</a> one ?', [
        ':url'   => Url::fromRoute('node.add', $arguments, $options)->toString(),
        '@entry' => $raw_match
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
   * @param array $g2_match
   *   The match array, containing a single node entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   */
  protected function indexRedirectSingleMatch(array $g2_match) {
    $status = $this->config[static::CONFIG_REDIRECT_STATUS];
    assert('is_int($status) && ($status === 201 || $status >= 300 && $status <= 399)', 'redirect is a redirect');
    assert('count($g2_match) === 0');
    /* @var \Drupal\Core\Entity\EntityInterface $node */
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
   * @return array<string,array|string>
   *   A render array.
   */
  protected function indexMatches($raw_match, array $g2_match) {
    $result = [
      '#prefix' => '<p>MATCHES</p>',
      '#theme' => 'g2_entries',
      '#raw_entry' => $raw_match,
      '#entries' => [],
    ];
    return $result;
  }

  /**
   * Controller for g2.entries.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The current route.
   * @param array $g2_match
   *   Unsafe. The entry for which to find matching G2 entries.
   *
   * @return array<string,array|string>|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Render array or redirect response.
   */
  public function indexAction(RouteMatchInterface $route, array $g2_match = NULL) {
    $raw_match = $route->getRawParameter('g2_match');

    switch (count($g2_match)) {
      case 0:
        $result = $this->indexNoMatch($raw_match);
        break;

      /* @noinspection PhpMissingBreakStatementInspection */
      case 1:
        $redirect = $this->config[static::CONFIG_REDIRECT_SINGLE];
        if ($redirect) {
          $result = $this->indexRedirectSingleMatch($g2_match);
          break;
        }
        /* Single match handled as any other non-0 number, so fall through. */

      default:
        $result = $this->indexMatches($raw_match, $g2_match);
        break;
    }
    if (!isset($result)) {
      $result = ['#plain_text' => 'Nix'];
    }
    return $result;

  }

  /**
   * Title callback for g2.entries.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The current route match.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup The page title.
   *   The page title.
   */
  public function indexTitle(RouteMatchInterface $route) {
    $raw_match = $route->getRawParameter('g2_match');
    return t('G2 entries matching %entry', ['%entry' => $raw_match]);
  }

  /**
   * Return a homonyms disambiguation page for homonym entries.
   *
   * The page is built:
   * - either by this module
   * - either from a site node (typically in PHP input format)
   *
   * When examining the code to build $entry, remember that
   * we need to obtain slashes, which drupal pre-processes.
   *
   * Note that we query and use n.title instead of using $entry2
   * in the results to obtain mixed case results when they exist.
   *
   * @param string $raw_entry
   *   The tainted user requested entry.
   * @param array $entries
   *   A possibly empty array of matches.
   *
   * @return string HTML: the themed "list of entries" page content.
   *   HTML: the themed "list of entries" page content.
   */
  protected function themeG2Entries($raw_entry, $entries = array()) {
    // The nid for the disambiguation page.
    $page_nid = variable_get(G2VARHOMONYMS, G2DEFHOMONYMS);

    if ($page_nid) {
      /* @var \Drupal\node\NodeInterface $page_node */
      $page_node = Node::load($page_nid);
      $result = node_view($page_node);
    }
    else {
      // Style more-link specifically.
      drupal_add_css(drupal_get_path('module', 'g2') . '/g2.css', 'module', 'all', FALSE);

      $vid = variable_get(G2VARHOMONYMSVID, G2DEFHOMONYMSVID);
      $rows = array();
      foreach ($entries as $nid => $node) {
        $path = 'node/' . $nid;
        $terms = array();
        $taxonomy = NULL;
        foreach ($node->taxonomy as $tid => $term) {
          if ($vid && $term->vid == $vid) {
            $terms[] = l($term->name, taxonomy_term_path($term));
          }
          $taxonomy = empty($terms)
            ? NULL
            : ' <span class="inline">(' . implode(', ', $terms) . ')</span>';

        }
        $teaser = strip_tags(check_markup($node->teaser, $node->format));
        $rows[] = t('!link!taxonomy: !teaser!more', array(
          '!link'     => l($node->title, $path),
          // Safe by construction.
          '!taxonomy' => $taxonomy,
          '!teaser'   => $teaser,
          '!more'     => theme('more_link', url($path),
            t('Full definition for @name: !teaser',
              array('@name' => $raw_entry, '!teaser' => $teaser))),
        ));
      }
      $result = theme('item_list', $rows, NULL, 'ul', array('class' => 'g2-entries'));
    }
    return $result;
  }

  /**
   * Alternative version.
   *
   * @param array $variables
   *   The theme variables.
   *
   * @return array
   *   A render array.
   */
  protected function zThemeG2Entries(array $variables) {
    $entries = $variables['entries'];
    $entry = filter_xss(arg(2));

    drupal_set_title(t('G2 Entries for %entry', array('%entry' => $entry)), PASS_THROUGH);

    // The nid for the disambiguation page.
    $page_nid = variable_get(G2::VARHOMONYMS, G2::DEFHOMONYMS);

    if ($page_nid) {
      $page_node = Node::load($page_nid);
      /* @var \Drupal\node\NodeInterface $page_node */
      $result = node_view($page_node);
    }
    else {
      $vid = variable_get(G2::VARHOMONYMSVID, G2::DEFHOMONYMSVID);
      $rows = array();
      foreach ($entries as $nid => $node) {
        $uri = entity_uri('node', $node);
        $terms = [];
        if (!isset($node->taxonomy)) {
          $node->taxonomy = [];
        }
        foreach ($node->taxonomy as $tid => $term) {
          if ($vid && $term->vid == $vid) {
            $terms[] = l($term->name, taxonomy_term_path($term));
          }
        }
        $taxonomy = empty($terms)
          ? NULL
          : ' <span class="inline">(' . implode(', ', $terms) . ')</span>';
        $teaser = isset($node->teaser)
          ? strip_tags(check_markup($node->teaser, $node->format))
          : NULL;
        $rows[] = t('!link!taxonomy: !teaser!more', [
          '!link' => l($node->title, $uri['path'], $uri['options']),
          // Safe by construction.
          '!taxonomy' => $taxonomy,
          '!teaser' => $teaser,
          '!more' => theme('more_link', [
              'url' => $uri['path'],
              'options' => $uri['options'],
              'title' => t('Full definition for @name: !teaser', [
                '@name' => $entry,
                '!teaser' => $teaser,
              ]),
            ]
          ),
        ]);
      }
      $result = [
        '#theme' => 'item_list',
        '#items' => $rows,
        '#title' => NULL,
        '#type' => 'ul',
        '#attributes' => array('class' => 'g2-entries'),
      ];
    }
    return $result;
  }

}
