<?php

namespace Drupal\g2\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\g2\Alphabar;
use Drupal\g2\G2;

use Drupal\node\NodeInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Main contains the G2 main page controller.
 */
class Main implements ContainerInjectionInterface {

  use StringTranslationTrait;

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
   * The core config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The module configuration.
   */
  public function __construct(
    EntityTypeManagerInterface $etm,
    Alphabar $alphabar,
    ConfigFactoryInterface $configFactory,
  ) {
    $this->alphabar = $alphabar;
    $this->configFactory = $configFactory;
    $this->etm = $etm;
  }

  /**
   * Build additional content on the page using an unpublished node.
   *
   * @param int $nid
   *   The node id.
   *
   * @return array
   *   A render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @deprecated in drupal:8.1.0 and is removed from drupal:8.2.0. Use a view.
   * @see https://www.drupal.org/project/g2/issues/3369887
   */
  protected function buildFromNode(int $nid): array {
    $node = $this->etm
      ->getStorage(G2::TYPE)
      ->load($nid);
    if (!($node instanceof NodeInterface)) {
      return [];
    }

    if ($node->body->isEmpty()) {
      return [];
    }

    // Simulate publishing.
    $node->setPublished();
    // Remove the title : we used it for the page title.
    $title = $node->label();
    $node->setTitle(NULL);

    $builder = $this->etm->getViewBuilder($node->getEntityTypeId());
    $text = $builder->view($node);
    $text['#original_title'] = $title;
    return $text;
  }

  /**
   * Build a default G2 main page body as a per-initial count list.
   *
   * @return array|array[]
   *   Render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function buildDefault(): array {
    $build = [
      'list' => [
        '#theme' => 'item_list',
        '#items' => [],
      ],
    ];
    $items = &$build['list']['#items'];
    $alphabar = $this->alphabar->getContents();
    if (empty($alphabar)) {
      return [];
    }
    $config = $this->configFactory->get(G2::CONFIG_NAME);
    $route = $config->get(G2::VARCONTROLLERINITIAL);

    $storage = $this->etm->getStorage(G2::TYPE);

    foreach (mb_str_split($alphabar) as $rune) {
      $n = $storage->getQuery()
        ->condition('type', G2::BUNDLE)
        ->condition('title', "${rune}%", 'LIKE')
        ->condition('status', NodeInterface::PUBLISHED)
        ->accessCheck()
        ->count()
        ->execute();
      $text = $this->formatPlural($n,
        "@initial - one definition",
        "@initial - @count definitions",
        ['@initial' => $rune],
      );
      $items[] = Link::createFromRoute($text, $route, ['g2_initial' => $rune]);
    }
    $type = G2::TYPE;
    $bundle = G2::BUNDLE;
    $confName = G2::CONFIG_NAME;
    $build['#cache'] = [
      'tags' => [
        "config:$confName",
        "${type}_list:${bundle}",
      ],
    ];
    return $build;
  }

  /**
   * The controller for the G2 main page.
   *
   * @return array
   *   A render array.
   */
  public function indexAction() {
    $alphaLinks = [
      '#theme' => 'g2_alphabar',
      '#alphabar' => $this->alphabar->getLinks(),
      // Set Row_length so that only an extremely long alphabar would wrap.
      '#row_length' => 2 << 16,
    ];

    $config = $this->configFactory->get(G2::CONFIG_NAME);
    $nid = $config->get(G2::VARMAINNID);
    $text = $nid
      ? $this->buildFromNode($nid)
      : $this->buildDefault();

    $ret = [
      '#theme' => 'g2_main',
      '#alphabar' => $alphaLinks,
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
    $alphabar = $container->get(G2::SVC_ALPHABAR);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $container->get(G2::SVC_CONF);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $etm */
    $etm = $container->get(G2::SVC_ETM);

    return new static($etm, $alphabar, $configFactory);
  }

}
