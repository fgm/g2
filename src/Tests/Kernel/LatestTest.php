<?php

namespace Drupal\g2\Tests\Kernel;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\g2\G2;
use Drupal\g2\Latest;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Class LatestTestCase covers the Latest service.
 *
 * @group G2
 */
class LatestTest extends KernelTestBase {

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }

  /**
   * Maximum number of latest entries in configuration.
   */
  const MAX = 2;

  const MODULES = [
    // Needed for routing.
    'system',
    // Service node_preview (proxied) needs user.private_tempstore.
    'user',
    // Needed by text.module.
    'field',
    'filter',
    // Needed by node module.
    'text',
    // Needed by g2.module.
    'node',
    'path_alias',
    'taxonomy',
    'views',
    'g2',
  ];

  /**
   * The modules to enable for the test.
   *
   * @var string[]
   */
  protected static $modules = self::MODULES;

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $config;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The G2 Latest(n) service.
   *
   * @var \Drupal\g2\Latest
   */
  protected Latest $latest;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(static::MODULES);

    // @see https://www.drupal.org/node/2605684
    $this->container->get('router.builder')->rebuild();

    $this->config = $this->container->get(G2::SVC_CONF);
    $this->etm = $this->container->get(G2::SVC_ETM);
    $this->latest = $this->container->get(G2::SVC_LATEST);
  }

  /**
   * Provider for testGetEntries and testGetLinks.
   *
   * Assumes for Latest(n) max_count = 2.
   *
   * @return array
   *   Cf. test.
   */
  public function providerLatest(): array {
    $tests = [
      'no nodes' => [[], [], []],
      'single node' => [['node1'], [], [1]],
      'over max' => [['node1', 'node2', 'node3'], [], [3, 2]],
      'updates inverted' => [['node1', 'node2', 'node3'], [3, 2, 1], [1, 2]],
    ];
    return $tests;
  }

  /**
   * Extra "business logic" setup, vs "Drupal logic" in setUp().
   *
   * @param array $titles
   *   The titles of nodes to create.
   * @param array $changes
   *   The nids of nodes to update after creation.
   * @param int $max
   *   The maximum entry count in configuration.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function doSetup(array $titles, array $changes, int $max): void {
    $this->config->getEditable(G2::CONFIG_NAME)
      ->set(G2::VARLATESTMAXCOUNT, $max)
      ->save();
    // Fake time to avoid having to sleep.
    $ts = time();
    foreach ($titles as $title) {
      $this->drupalCreateNode([
        'title' => $title,
        'type' => G2::BUNDLE,
        'created' => $ts,
        'changed' => $ts,
      ]);
      // Drupal created and changed base fields only have second resolution,
      // so we need to make sure the changed fields will actually be ordered
      // instead of falling in the same second.
      $ts++;
    }
    if (!empty($changes)) {
      $storage = $this->etm->getStorage(G2::TYPE);
      $nodes = $storage->loadMultiple($changes);
      foreach ($nodes as $nid => $node) {
        // Same issue as during creation.
        $node->g2_complement->value = sprintf("test update on node %d", $nid);
        $node->changed->value = $ts;
        $ts++;
        $node->save();
      }
    }
  }

  /**
   * Test the getEntries method.
   *
   * @dataProvider providerLatest
   */
  public function testGetEntries(array $titles, array $changes, array $expected) {
    $n = self::MAX + 3;
    $this->doSetup($titles, $changes, self::MAX);
    $entries = $this->latest->getEntries($n);
    $actual = array_keys($entries);
    $this->assertLessThanOrEqual(self::MAX, count($entries));
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test the getLinks method.
   *
   * @dataProvider providerLatest
   */
  public function testGetLinks(array $titles, array $changes, array $expected) {
    $n = self::MAX + 3;
    $this->doSetup($titles, $changes, self::MAX);
    $links = $this->latest->getLinks($n);
    $expectedTags = array_map(fn($nid) => "node:${nid}", $expected);
    sort($expectedTags);
    $tags = array_map(function (Link $link) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $link->getUrl()->getOption('entity');
      $this->assertInstanceOf(NodeInterface::class, $node);
      return $node->getCacheTags();
    }, $links);
    $actualTags = [];
    array_walk_recursive($tags, function ($tag) use (&$actualTags) {
      $actualTags[] = $tag;
    });
    sort($actualTags);
    $this->assertEquals($expectedTags, $actualTags);
  }

}
