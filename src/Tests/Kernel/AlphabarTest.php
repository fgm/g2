<?php

namespace Drupal\g2\Tests\Kernel;

use Drupal\g2\Alphabar;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class AlphabarTestCase covers the Alphabar service.
 *
 * @group G2
 */
class AlphabarTest extends KernelTestBase {

  const MODULES = [
    // Needed for routing.
    'system',
    // Service node_preview (proxied) needs user.private_tempstore.
    'user',
    // Needed by text.module.
    'field',
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
    $this->installConfig(static::MODULES);

    // @see https://www.drupal.org/node/2605684
    $this->container->get('router.builder')->rebuild();

    $this->alphabar = $this->container->get('g2.alphabar');
  }

  /**
   * Tests alphabar generation.
   */
  public function testAlphabar() {
    $this->assertEquals(1, 1);
    $links = $this->alphabar->getLinks();
    $this->assertTrue(is_array($links));

    $expected = mb_strlen($this->alphabar->getContents());
    $actual = count($links);
    $this->assertEquals($expected, $actual,
      'The number of links matches the number of runes in alphabar.contents');
  }

}
