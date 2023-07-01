<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\Kernel;

use Drupal\g2\G2;
use Drupal\g2\Matcher;
use Drupal\g2\Tests\Helpers;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Class MatcherTest covers the Matcher service.
 *
 * @group G2
 */
class MatcherTest extends KernelTestBase {

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }

  /**
   * Maximum number of G2 entries to create.
   *
   * This is 1/10 the order of magnitude of the Riff glossary of computing.
   */
  const MAX = 600;

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
   * The g2.matcher service.
   *
   * @var \Drupal\g2\Matcher
   */
  protected Matcher $matcher;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(static::MODULES);

    $this->matcher = $this->container->get(G2::SVC_MATCHER);
  }

  /**
   * Benchmark rebuilding a 6k entries glossary.
   *
   * Note that the setup required for this test takes really long
   * (about 2 hours on a M1 Max macBook Pro).
   *
   * @throws \Exception
   */
  public function testBenchmarkRebuild() {
    Helpers::skipBenchmarkUnlessRequested();

    $rand = $this->getRandomGenerator();
    // Fake time to avoid having to sleep.
    $t0 = microtime(TRUE);
    $ts = time();
    for ($i = 0; $i < self::MAX; $i++) {
      $title = $rand->word(AhoCorasickTest::MAX_LEN);
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
    $t1 = microtime(TRUE);
    dump(sprintf("Built %d test nodes in %d secondes", self::MAX, $t1 - $t0));
    $this->matcher->rebuild();
    $t2 = microtime(TRUE);
    $msec = ($t2 - $t1) * 1E3;
    $n = count($this->matcher->getMultiStringMatcher()->getKeywords());
    dump(sprintf("%d msec to rebuild matcher for %d words: %.3f msec/word",
      $msec, $n, $msec / $n));
    $this->assertTrue(TRUE);
  }

}
