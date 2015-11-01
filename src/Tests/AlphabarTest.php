<?php
/**
 * @file
 * Contains AlphabarTest.
 */

namespace Drupal\g2\Tests;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class AlphabarTestCase covers the Alphabar service.
 *
 * @group G2
 */
class AlphabarTest extends KernelTestBase {

  public static $modules = ['g2'];

  const MODULES = ['g2'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(static::MODULES);
  }

  /**
   * Tests alphabar generation.
   */
  public function testAlphabar() {
    $config = $this->config('g2.settings')->get('block');
  }
}
