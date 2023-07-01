<?php

declare(strict_types = 1);

namespace Drupal\g2;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * TestLogger is a test helper that logs to a public array.
 */
class TestLogger implements LoggerInterface {
  use LoggerTrait;

  /**
   * The individual log entries, in insertion order.
   *
   * @var array
   */
  public array $entries = [];

  /**
   * {@inheritDoc}
   */
  public function log($level, $message, array $context = []) {
    $this->entries[] = [$level, $message, $context];
  }

  /**
   * Count the number of entries matching a level boundary condition.
   *
   * @param int $level
   *   The boundary level by which to filter.
   * @param string $op
   *   Op may be '=' for an exact match or '<=' for any lower or equal level
   *   (more severe), or '>=' for any higher or equal level (less severe).
   *
   * @return int
   *   The number of matching entries.
   */
  public function countByLevel(int $level, string $op = '<='): int {
    $count = 0;
    foreach ($this->entries as $entry) {
      $match = FALSE;
      switch ($op) {
        // Exact level.
        case '=':
          $match = $entry[0] === $level;
          break;

        // More severe of equal.
        case '<=':
          $match = $entry[0] <= $level;
          break;

        // Less severe or equal.
        case '>=':
          $match = $entry[0] >= $level;
          break;
      }
      if ($match) {
        $count++;
      }
    }
    return $count;
  }

}
