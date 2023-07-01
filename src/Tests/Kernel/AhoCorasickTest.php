<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\Kernel;

use AhoCorasick\MultiStringMatcher;
use Drupal\g2\G2;
use Drupal\g2\Matcher;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class G2UnitTest provides unit test for G2 methods.
 *
 * Its purpose is to benchmark MSM rebuild vs the time it takes to retrieve
 * it from KV storage. Retrieving is faster in all cases.
 *
 * See docs/rebuilding-vs-retrieving.png for details.
 *
 * @link https://docs.google.com/spreadsheets/d/1L7qUc7fZQjkJpj83MuR5uPN9C8nNDOK3Zj8h6sqXFcg
 *
 * @group G2
 */
class AhoCorasickTest extends KernelTestBase {

  const MAX_LEN = 255;

  /**
   * The number of nodes to generate, as a power of two (i.e. 5 => 32).
   */
  const MAX_SCALE = 12;

  /**
   * Data provider for both tests.
   *
   * Note that the number of entries actually in the MSM may be less than the
   *  number of generated entries due to random string generation collisions.
   *
   * @return array
   *   A map of titles by number of entries generated.
   */
  public function providerTitles(): array {
    $rows = [];
    $rand = $this->getRandomGenerator();
    // Note we use "<"=, not "<" because this is a scale.
    for ($scale = 0; $scale <= self::MAX_SCALE; $scale++) {
      $n = pow(2, $scale);
      $row = [];
      for ($i = 0; $i < $n; $i++) {
        $len = mt_rand(0, self::MAX_LEN);
        $title = $rand->word($len);
        $row[] = $title;
      }
      $rows["$n words"] = [$row];
    }
    return $rows;
  }

  /**
   * Benchmark rebuilding the MSM on each case.
   *
   * @dataProvider providerTitles
   */
  public function testBenchmarkKeyValue(array $titles) {
    $t0 = microtime(TRUE);
    $msm = new MultiStringMatcher($titles);
    $t1 = microtime(TRUE);
    $µsec = ($t1 - $t0) * 1E6;
    $n = count($msm->getKeywords());
    dump(sprintf("%d µsec to build a MSM for %d words: %.3f µsec/word",
      $µsec, $n, $µsec / $n));
    $this->assertTrue(TRUE);
  }

  /**
   * Benchmark retrieving the MSM from KV.
   *
   * @dataProvider providerTitles
   */
  public function testBenchmarkRebuild(array $titles) {
    $ref = new MultiStringMatcher($titles);
    $coll = $this->keyValue->get(Matcher::COLLECTION);
    $coll->set(G2::KV_TITLES, $ref);
    $t0 = microtime(TRUE);
    $msm = $coll->get(G2::KV_TITLES);
    $t1 = microtime(TRUE);
    $µsec = ($t1 - $t0) * 1E6;
    $n = count($msm->getKeywords());
    dump(sprintf("%d µsec to retrieve a MSM for %d words: %.3f µsec/word",
      $µsec, $n, $µsec / $n));
    $this->assertTrue(TRUE);
  }

}
