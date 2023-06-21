<?php

namespace Drupal\g2\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test helpers.
 */
class Helpers {

  /**
   * Skip the calling test unless PHPunit was called with --benchmarks.
   */
  public static function skipBenchmarkUnlessRequested(): void {
    $skip = !($_SERVER['G2_BENCHMARKS'] ?? FALSE);
    if ($skip) {
      TestCase::markTestSkipped("Set environment variable G2_BENCHMARKS to true when calling PHPunit in order to run benchmarks: these are very slow.");
    }
  }

}
