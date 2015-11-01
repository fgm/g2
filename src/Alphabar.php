<?php
/**
 * @file
 * Contains Alphabar.
 */

namespace Drupal\g2;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Utility\LinkGenerator;

/**
 * Class Alphabar provides a list of links to entries-by-initial pages.
 */
class Alphabar {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGenerator
   */
  protected $linkGenerator;

  /**
   * The entity query service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $query;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param \Drupal\Core\Utility\LinkGenerator $link_generator
   *   The link generator service.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query
   *   The entity query service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator service.
   */
  public function __construct(ConfigFactoryInterface $config, LinkGenerator $link_generator, QueryFactory $query, UrlGeneratorInterface $url_generator) {
    $this->config = $config;
    $this->linkGenerator = $link_generator;
    $this->query = $query;
    $this->urlGenerator = $url_generator;
  }

  /**
   * Return an array of links to entries-by-initial pages.
   */
  public function getLinks() {
    $raw_alphabar = $this->config('g2.alphabar.contents');
    $result = [];
    $options = [
      // So alphabar can be used outside site pages.
      'absolute' => TRUE,
      // To preserve the pre-encoded path.
      'html'     => TRUE,
    ];

    $route_name = $this->config('g2.settings.controller.initial');

    for ($i = 0; $i < Unicode::strlen($raw_alphabar); $i++) {
      $initial = Unicode::substr($raw_alphabar, $i, 1);
      $path = G2::encodeTerminal($initial);
      $parameters = ['g2_match' => $path];
      $url = $this->urlGenerator->generateFromRoute($route_name, $parameters, $options);
      $result[] = $this->linkGenerator->generate($initial, $url);
    }

    return $result;
  }

}
