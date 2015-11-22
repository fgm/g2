<?php

/**
 * @file
 * Contains G2 Requirements.
 *
 * @copyright 2005-2015 Frédéric G. Marand, for Ouest Systemes Informatiques.
 */

namespace Drupal\g2;


use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Class Requirements contains the hook_requirements() checks.
 */
class Requirements implements ContainerInjectionInterface {

  /**
   * The g2.settings configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  protected $result = [];

  protected $routeProvider;

  /**
   * Requirements constructor.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The g2.settings configuration.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The router.route_provider service.
   */
  public function __construct(ImmutableConfig $config, RouteProviderInterface $route_provider) {
    $this->config = $config;
    $this->routeProvider = $route_provider;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * This is a factory method that returns a new instance of this class. The
   * factory should pass any needed dependencies into the constructor of this
   * class, but not the container itself. Every call to this method must return
   * a new instance of this class; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   *
   * @return static
   *   A new instance every time.
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    $config = $config_factory->get('g2.settings');

    /* @var \Drupal\Core\Routing\RouteProvider $route_provider */
    $route_provider = $container->get('router.route_provider');
    return new static($config, $route_provider);
  }

  /**
   * Check whether a node is valid.
   *
   * @param string $key
   *   The config key for the route to validate.
   * @param \Drupal\Component\Render\MarkupInterface $title
   *   The requirement check title.
   *
   * @return array
   *   A hook_requirements value.
   */
  protected function checkNid($key, MarkupInterface $title) {
    $result = ['title' => $title];
    $main = $this->config->get($key);

    assert('is_numeric($nid)');
    if ($main) {
      $node = Node::load($main);
      if (!($node instanceof NodeInterface)) {
        $result += [
          'value' => t('The chosen node must be a valid one, or 0: "@nid" is not a valid node id.',
            ['@nid' => $main]),
          'severity' => REQUIREMENT_ERROR,
        ];
      }
      else {
        $url = Url::fromRoute('entity.node.canonical', ['node' => $main])
          ->toString();
        $result += [
          'value' => t('Valid node: <a href=":url">:url</a>', [':url' => $url]),
          'severity' => REQUIREMENT_OK,
        ];
      }
    }
    else {
      $result += [
        'value' => t('Node set to empty'),
        'severity' => REQUIREMENT_INFO,
      ];
    }
    return $result;
  }

  /**
   * Check whether a route is valid.
   *
   * @param string $key
   *   The config key for the route to validate.
   * @param \Drupal\Component\Render\MarkupInterface $title
   *   The requirement check title.
   *
   * @return array
   *   A hook_requirements() value.
   */
  protected function checkRoute($key, MarkupInterface $title) {
    $result = ['title' => $title];
    $name = $this->config->get($key);

    $arguments = ['%route' => $name];
    try {
      $route = $this->routeProvider->getRouteByName($name);
      if ($route->hasOption('parameters')) {
        $value = t('Valid parametric route %route', $arguments);
      }
      else {
        $value = t('Valid static route <a href="url">%route</a>', $arguments + [
            ':url' => Url::fromRoute($name)->toString(),
        ]);
      }
      $result += [
        'value' => $value,
        'severity' => REQUIREMENT_OK,
      ];
    }
    catch (RouteNotFoundException $e) {
      $result += array(
        'value' => t('The chosen route is not available: %route', $arguments),
        'severity' => REQUIREMENT_ERROR,
      );
    }
    return $result;
  }

  /**
   * Perform controller requirements checks.
   */
  public function checkControllers() {
    $this->result['main.nid'] = $this->checkNid('controller.main.nid', t('G2 main page node id'));
    $this->result['main.route'] = $this->checkRoute('controller.main.route', t('G2 main page route'));
    $this->result['homonyms.nid'] = $this->checkNid('controller.homonyms.nid', t('G2 homonyms disambiguation page node id'));
    $this->result['homonyms.route'] = $this->checkRoute('controller.homonyms.route', t('G2 homonyms disambiguation page route'));
  }

  /**
   * Return the check results.
   *
   * @return array
   *   In the hook_requirements() format.
   */
  public function getResult() {
    return $this->result;
  }

}
