<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\Functional;

use Drupal\Core\Url;
use Drupal\g2\G2;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the WOTD feed as a user agent.
 *
 * @group G2
 */
class WotdFeedTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [G2::NAME];

  /**
   * Test for issue #3371672: WOTD feed must work even without entries.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testFeed3371672() {
    $url = Url::fromRoute(G2::ROUTE_FEED_WOTD)->toString();
    $this->drupalGet($url);
    $this->assertSession()->statusCodeEquals(200);
  }

}
