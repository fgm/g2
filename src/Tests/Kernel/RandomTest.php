<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\Kernel;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\g2\G2;
use Drupal\g2\Random;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests for the random service.
 *
 * @group G2
 */
class RandomTest extends KernelTestBase {

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }

  /**
   * The title of the "random" node.
   */
  const TITLE_FOO = 'Foo';

  /**
   * The title of the stored "previous random" node.
   */
  const TITLE_STORED = 'Stored';

  /**
   * The title of the WOTD node.
   */
  const TITLE_WOTD = 'WOTD';

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
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $db;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * The g2.random service.
   *
   * @var \Drupal\g2\Random
   */
  protected Random $random;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', []);
    $this->installConfig(static::MODULES);

    // @see https://www.drupal.org/node/2605684
    $this->container->get('router.builder')->rebuild();

    $this->config = $this->container->get(G2::SVC_CONF);
    $this->db = $this->container->get('database');
    $this->etm = $this->container->get(G2::SVC_ETM);
    $this->state = $this->container->get(G2::SVC_STATE);
    $this->random = $this->container->get(G2::SVC_RANDOM);
  }

  /**
   * Data provider for testGet().
   *
   * @return array[]
   *   For each test case:
   *   - [nodes to create].
   *   - enable random store, only makes sense if there is a storable node.
   *   - expect exception.
   *   - expect title.
   */
  public function providerGet() {
    return [
      'no nodes, disabled' => [[], FALSE, TRUE, ''],
      'wotd, disabled' => [[static::TITLE_WOTD], FALSE, TRUE, ''],
      'wotd+any, disabled' => [
        [static::TITLE_FOO, static::TITLE_WOTD],
        FALSE,
        FALSE,
        static::TITLE_FOO,
      ],
      '3 nodes, enabled' => [
        [static::TITLE_FOO, static::TITLE_STORED, static::TITLE_WOTD],
        TRUE,
        FALSE,
        static::TITLE_FOO,
      ],
    ];
  }

  /**
   * Test Random::get.
   *
   * @dataProvider providerGet
   */
  public function testGet(
    array $titles,
    bool $storeRandom,
    bool $expectException,
    string $expectedTitle,
  ) {
    // Setup: content.
    foreach ($titles as $title) {
      $this->drupalCreateNode([
        'title' => $title,
        'type' => G2::BUNDLE,
      ]);
    }

    // Setup: config+state > random.store.
    $conf = $this->config->getEditable(G2::CONFIG_NAME);
    $conf->set(G2::VARRANDOMSTORE, $storeRandom);
    $conf->save();
    if ($storeRandom && in_array(static::TITLE_STORED, $titles)) {
      $this->state->set(G2::VARRANDOMENTRY, static::TITLE_STORED);
    }

    // Setup:= config: wotd.entry.
    if (in_array(static::TITLE_WOTD, $titles)) {
      /** @var \Drupal\Core\Config\Config $conf */
      $conf = $this->config->getEditable(G2::CONFIG_NAME);
      $wotd = (int) key($this->etm
        ->getStorage(G2::TYPE)
        ->loadByProperties(['title' => static::TITLE_WOTD]));
      $conf->set(G2::VARWOTDENTRY, $wotd);
      $conf->save();
    }

    // Perform test.
    $gotException = FALSE;
    try {
      $random = $this->random->get();
      $actualTitle = $random->label();
    }
    catch (\Exception $e) {
      $gotException = TRUE;
      if (!$expectException) {
        $this->fail(sprintf("Unexpected exception: %s", $e));
      }
    }
    finally {
      // At this point, gotException == expectException.
      if ($gotException) {
        return;
      }
    }
    if ($this->assertEquals($expectedTitle, $actualTitle)) {
      $this->fail(sprintf('Expected "%s" but got "%s"', $expectedTitle, $actualTitle));
    }
  }

}
