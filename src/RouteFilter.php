<?php

declare(strict_types = 1);

namespace Drupal\g2;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;

/**
 * RouteFilter provides a list of routes usable for the G2 controllers.
 */
class RouteFilter {

  /**
   * The core router.route_provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected RouteProviderInterface $routeProvider;

  /**
   * The core current_route_match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The core router.route_provider service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The core current_route_match service.
   */
  public function __construct(
    RouteProviderInterface $routeProvider,
    RouteMatchInterface $routeMatch,
  ) {
    $this->routeMatch = $routeMatch;
    $this->routeProvider = $routeProvider;
  }

  /**
   * Build a list of route names.
   *
   * These routes must take the number of parameters specified, and be
   * accessible at least with the GET HTTP method.
   *
   * @param string $start
   *   The beginning of the route name.
   * @param int $exactParamCount
   *   The number of parameters the route must accept.
   * @param bool $acceptForms
   *   Accept routes with a _form or _entity_form in defaults.
   *   If FALSE, only accept routes with a _controller or _entity_list, and the
   *   special route <front>.
   *
   * @return array
   *   A map of routes by name.
   */
  public function getRoutes(
    string $start,
    int $exactParamCount = 0,
    bool $acceptForms = FALSE
  ): array {
    $all = $this->routeProvider->getAllRoutes();
    $routes = [];
    foreach ($all as $name => $route) {
      if (strpos($name, $start) !== 0) {
        continue;
      }
      if ($route->hasOption('parameters')) {
        $params = $route->getOption('parameters');
        if (count($params) !== $exactParamCount) {
          continue;
        }
      }
      // G2 routes are used to display pages.
      if (!in_array('GET', $route->getMethods())) {
        continue;
      }
      $hasController = $route->hasDefault('_controller');
      $hasList = $route->hasDefault('_entity_list');
      $hasForm = $route->hasDefault('_form');
      $hasEntityForm = $route->hasDefault('_entity_form');
      // G2 routes need one of those.
      if (!$hasController && !$hasEntityForm && !$hasForm && !$hasList) {
        continue;
      }
      if (!$acceptForms && ($hasEntityForm || $hasForm)) {
        continue;
      }

      $routes[$name] = $route;
    }

    return $routes;
  }

  /**
   * Define a G2 "context" for the current route match.
   *
   * @return bool
   *   TRUE if the current route match is one of the G2 routes or a canonical
   *   view of a G2 entry node.
   */
  public function isG2Route(): bool {
    $name = $this->routeMatch->getRouteName();
    if (str_starts_with($name, 'g2.')) {
      return TRUE;
    }
    if ($name != G2::ROUTE_NODE_CANONICAL) {
      return FALSE;
    }
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');
    return $node->bundle() === G2::BUNDLE;
  }

}
