<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\FunctionalJavascript;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\g2\G2;
use Drupal\g2\Tests\Kernel\AhoCorasickTest;
use Drupal\g2\Top;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\user\Entity\Role;

/**
 * Tests that statistics works.
 *
 * @group system
 */
class TopTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'g2',
    'language',
    'node',
    'statistics',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Node for tests.
   *
   * @var \Drupal\node\Entity\NodeInterface[]
   */
  protected array $nodes;

  /**
   * The g2.top service.
   *
   * @var \Drupal\g2\Top
   */
  protected Top $top;

  /**
   * The core entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $etm;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('statistics.settings')
      ->set('count_content_views', 1)
      ->save();

    Role::load(AccountInterface::ANONYMOUS_ROLE)
      ->grantPermission('view post access counter')
      ->save();

    // Add another language to enable multilingual path processor.
    ConfigurableLanguage::create(['id' => 'xx', 'label' => 'Test language'])->save();
    $this->config('language.negotiation')->set('url.prefixes.en', 'en')->save();
    $this->etm = $this->container->get(G2::SVC_ETM);
    $this->top = $this->container->get(G2::SVC_TOP);
  }

  /**
   * Create 3 g2_entry nodes.
   */
  protected function createNodes() {
    $rand = $this->getRandomGenerator();
    $ts = time() - 10;
    for ($i = 0; $i < 3; $i++) {
      $title = $rand->word(AhoCorasickTest::MAX_LEN);
      $now = $ts++;
      $this->drupalCreateNode([
        'title' => $title,
        'type' => G2::BUNDLE,
        'created' => $now,
        'changed' => $now,
      ]);
    }
  }

  /**
   * Tests that statistics work for the TopN block.
   */
  public function testTopGetLinks() {
    $this->config(G2::CONFIG_NAME)
      ->set(G2::VARTOPMAXCOUNT, 10)
      ->save();

    // No links without content.
    $links = $this->top->getLinks(10);
    $this->assertEmpty($links);

    // No links with content not yet visited.
    $this->createNodes();
    $links = $this->top->getLinks(10);
    $this->assertEmpty($links);

    // Count and order match.
    for ($nid = 1; $nid <= 3; $nid++) {
      $times = pow(2, $nid - 1);
      for ($count = 0; $count < $times; $count++) {
        $this->visitNode($nid);
      }
    }
    $links = $this->top->getLinks(10);
    $this->assertCount(3, $links);
    $this->assertMatchesRegularExpression("#node/3#", $links[0]->__toString());
    $this->assertMatchesRegularExpression("#node/2#", $links[1]->__toString());
    $this->assertMatchesRegularExpression("#node/1#", $links[2]->__toString());

    // Count is limited by the number passed.
    $links = $this->top->getLinks(2);
    $this->assertCount(2, $links);
    $this->assertMatchesRegularExpression("#node/3#", $links[0]->__toString());
    $this->assertMatchesRegularExpression("#node/2#", $links[1]->__toString());

    // Count is limited by the service config.
    $this->config(G2::CONFIG_NAME)
      ->set(G2::VARTOPMAXCOUNT, 2)
      ->save();
    $links = $this->top->getLinks(10);
    $this->assertCount(2, $links);
    $this->assertMatchesRegularExpression("#node/3#", $links[0]->__toString());
    $this->assertMatchesRegularExpression("#node/2#", $links[1]->__toString());
  }

  /**
   * Visit nodes.
   *
   * @param int $nid
   *   The node to visit.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function visitNode(int $nid) {
    $this->drupalGet("/node/$nid");
    // Wait while statistics module send ajax request.
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

}
