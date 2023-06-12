<?php

namespace Drupal\g2\Tests\Kernel;

use Drupal\g2\Alphabar;
use Drupal\g2\G2;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Class AlphabarTestCase covers the Alphabar service.
 *
 * @group G2
 */
class AlphabarTest extends KernelTestBase {

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }

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
   * The G2 Alphabar service.
   *
   * @var \Drupal\g2\Alphabar
   */
  protected Alphabar $alphabar;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', []);
    $this->installConfig(static::MODULES);

    // @see https://www.drupal.org/node/2605684
    $this->container->get('router.builder')->rebuild();

    $this->alphabar = $this->container->get(G2::SVC_ALPHABAR);
  }

  /**
   * Tests alphabar generation.
   */
  public function testAlphabar() {
    $links = $this->alphabar->getLinks();
    $this->assertTrue(is_array($links));

    $expected = mb_strlen($this->alphabar->getContents());
    $actual = count($links);
    $this->assertEquals($expected, $actual,
      'The number of links matches the number of runes in the alphabar contents');
  }

  /**
   * Provider for testRebuildAlphabar.
   *
   * @return array[]
   *   Cf. test.
   */
  public function providerRebuildAlphabar(): array {
    $tests = [
      // Empty case.
      'no nodes' => [[], ''],
      // Check ordering.
      'pigs (three different ones)' => [
        ['whitehouse', 'pig man', 'rat bag'],
        'PRW',
      ],
      // Check deduplication.
      'dogs' => [['dogppool', 'dogmatix', 'gromit', 'dogbert'], 'DG'],
    ];
    return $tests;
  }

  /**
   * Test the automatic alphabar generation.
   *
   * @dataProvider providerRebuildAlphabar
   */
  public function testRebuildAlphabar(array $titles, string $expected) {
    foreach ($titles as $title) {
      $this->drupalCreateNode([
        'title' => $title,
        'type' => G2::BUNDLE,
      ]);
    }
    $counts = $this->alphabar->fromEntries();
    $actual = implode(array_keys($counts));

    $this->assertEquals($expected, $actual);
  }

}
