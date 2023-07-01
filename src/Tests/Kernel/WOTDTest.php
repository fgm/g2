<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\Kernel;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\g2\G2;
use Drupal\g2\TestLogger;
use Drupal\g2\WOTD;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests for the WOTD service.
 *
 * @group G2
 */
class WOTDTest extends KernelTestBase {

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }

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
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * The test logger, not the logger.channel.g2 service.
   *
   * @var \Drupal\g2\TestLogger
   */
  protected TestLogger $testLogger;

  /**
   * The g2.wotd service.
   *
   * @var \Drupal\g2\WOTD
   */
  protected WOTD $wotd;

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
    $this->etm = $this->container->get(G2::SVC_ETM);
    $this->state = $this->container->get(G2::SVC_STATE);
    $this->wotd = $this->container->get(G2::SVC_WOTD);

    /** @var \Drupal\g2\TestLogger $testLogger */
    $this->testLogger = $this->container->get(G2::SVC_TEST_LOGGER);
    /** @var \Drupal\Core\Logger\LoggerChannelInterface $logger */
    $logger = $this->container->get(G2::SVC_LOGGER);
    $logger->addLogger($this->testLogger);
  }

  /**
   * Data provider for testGet().
   *
   * @return array[]
   *   Cf. test.
   */
  public function providerGet(): array {
    return [
      'not stored' => [FALSE, TRUE, ''],
      'stored' => [TRUE, FALSE, static::TITLE_WOTD],
    ];
  }

  /**
   * Test WOTD::get.
   *
   * @dataProvider providerGet
   */
  public function testGet(
    bool $isWOTDStored,
    bool $expectNull,
    string $expectedTitle,
  ) {
    // Setup: content.
    $wotd = $this->drupalCreateNode([
      'title' => static::TITLE_WOTD,
      'type' => G2::BUNDLE,
    ]);

    // Setup: config.
    $conf = $this->config->getEditable(G2::CONFIG_NAME);
    if ($isWOTDStored) {
      $conf->set(G2::VARWOTDENTRY, $wotd->id());
    }
    else {
      $conf->clear(G2::VARWOTDENTRY);
    }
    $conf->save();

    // Perform test.
    try {
      $actual = $this->wotd->get();
    }
    catch (\Exception $e) {
      $this->fail(sprintf("Unexpected exception: %s", $e));
    }
    if ($expectNull) {
      if (!is_null($actual)) {
        $this->fail('Expected no WOTD but got %s', $actual->label());
      }
      return;
    }
    $actualTitle = $actual->label();
    if ($this->assertEquals($expectedTitle, $actualTitle)) {
      $this->fail(sprintf('Expected "%s" but got "%s"', $expectedTitle, $actualTitle));
    }
  }

  /**
   * Data provider for testIsAutoChangeEnabled.
   *
   * @return array[]
   *   Cf. test.
   */
  public function providerIsAutoChangeEnabled(): array {
    return [
      "disabled" => [TRUE, TRUE],
      "enabled" => [FALSE, FALSE],
      "not set" => [NULL, FALSE],
    ];
  }

  /**
   * Test WOTD::isAutoChangeEnabled.
   *
   * @dataProvider providerIsAutoChangeEnabled
   */
  public function testIsAutoChangeEnabled(?bool $input, bool $expected) {
    $config = $this->config->getEditable(G2::CONFIG_NAME);
    if (!isset($input)) {
      $config->clear(G2::VARWOTDAUTOCHANGE);
    }
    else {
      $config->set(G2::VARWOTDAUTOCHANGE, $input);
    }
    $config->save();
    $actual = $this->wotd->isAutoChangeEnabled();
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testAutochange.
   *
   * @return \array[][]
   *   Cf. test.
   */
  public function providerAutoChange(): array {
    $now = \DateTimeImmutable::createFromFormat('U', (string) (time() - 86400));
    $yesterday = $now->sub(new \DateInterval("P1D"));
    return [
      'happy' => [
        [
          RandomTest::TITLE_FOO,
          RandomTest::TITLE_WOTD,
        ],
        TRUE,
        $yesterday,
        FALSE,
        RandomTest::TITLE_FOO,
      ],
      'sad no node available' => [
        [RandomTest::TITLE_WOTD],
        TRUE,
        $yesterday,
        TRUE,
        '',
      ],
    ];
  }

  /**
   * Test WOTD::autoChange.
   *
   * @dataProvider providerAutoChange
   *
   * @throws \Exception
   */
  public function testAutochange(
    array $nodesToCreate,
    bool $isEntrySet,
    \DateTimeImmutable $entryDate,
    bool $expectError,
    string $expectedTitle,
  ) {
    $nodesByTitle = [];
    foreach ($nodesToCreate as $title) {
      $nodesByTitle[$title] = $this->drupalCreateNode([
        'title' => $title,
        'type' => G2::BUNDLE,
      ]);
    }
    $this->assertNotEmpty($nodesByTitle[RandomTest::TITLE_WOTD]);
    if ($isEntrySet) {
      $this->assertNotEmpty($entryDate);
      $this->config
        ->getEditable(G2::CONFIG_NAME)
        ->set(G2::VARWOTDENTRY, $nodesByTitle[RandomTest::TITLE_WOTD]->id())
        ->save();
      $this->state->set(G2::VARWOTDDATE, $entryDate->format(WOTD::DATE_STORAGE_FORMAT));
    }

    $this->wotd->autoChange();

    $errorCount = $this->testLogger->countByLevel(RfcLogLevel::ERROR);
    if ($expectError) {
      $this->assertEquals(1, $errorCount);
      return;
    }
    $this->assertEquals(0, $errorCount);

    if (!$expectedTitle) {
      return;
    }
    $actualNid = $this->config
      ->get(G2::CONFIG_NAME)
      ->get(G2::VARWOTDENTRY);
    $this->assertNotEmpty($actualNid);
    $actualNode = $this->etm
      ->getStorage(G2::TYPE)
      ->load($actualNid);
    $this->assertNotEmpty($actualNode);
    $actualTitle = $actualNode->label();
    $this->assertEquals($expectedTitle, $actualTitle);
  }

}
