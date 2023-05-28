<?php

namespace Drupal\g2\Controller;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\g2\Alphabar;
use Drupal\g2\G2;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Main contains the G2 main page controller.
 */
class Main implements ContainerInjectionInterface {
  /**
   * Title of the G2 main page.
   */
  const TITLE = 'G2 glossary main page';

  /**
   * The g2.alphabar service.
   *
   * @var \Drupal\g2\Alphabar
   */
  protected $alphabar;

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * Main constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $etm
   *   The entity_type.manager service.
   * @param \Drupal\g2\Alphabar $alphabar
   *   The g2.alphabar service.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The module configuration.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    Alphabar $alphabar,
    ImmutableConfig $config,
  ) {
    $this->alphabar = $alphabar;
    $this->config = $config;
    $this->etm = $etm;
  }

  /**
   * The controller for the G2 main page.
   *
   * @return array
   *   A render array.
   */
  public function indexAction() {
    $alphabar = [
      '#theme' => 'g2_alphabar',
      '#alphabar' => $this->alphabar->getLinks(),
      // Set Row_length so that only an extremely long alphabar would wrap.
      '#row_length' => 2 << 16,
    ];

    $generator = $this->config->get('controller.main.nid');
    $node = $this->etm->getStorage('node')->load($generator);
    if ($node instanceof NodeInterface) {
      $title = $node->label();

      // @todo Ensure we still want to override the site name.
      /* _g2_override_site_name(); */

      if (!$node->body->isEmpty()) {
        // Simulate publishing.
        $node->setPublished(Node::PUBLISHED);
        // Remove the title : we used it for the page title.
        $node->setTitle(NULL);
        $builder = $this->etm->getViewBuilder($node->getEntityTypeId());
        $text = $builder->view($node);
      }
      else {
        // Empty or missing body field.
        $text = [];
      }
    }
    else {
      // Node not found.
      $text = [];
    }

    $ret = [
      '#theme' => 'g2_main',
      '#alphabar' => $alphabar,
      '#text' => $text,
    ];

    if (!empty($title)) {
      $ret['#title'] = $title;
    }

    return $ret;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\g2\Alphabar $alphabar */
    $alphabar = $container->get('g2.alphabar');

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $etm */
    $etm = $container->get('entity_type.manager');

    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $config_factory->get(G2::CONFIG_NAME);

    return new static($etm, $alphabar, $config);
  }

}
