<?php

declare(strict_types = 1);

namespace Drupal\g2\Tests\Unit;

use AhoCorasick\MultiStringMatcher;
use Drupal\g2\Matcher;
use Drupal\Tests\UnitTestCase;

/**
 * Class MatcherTest provides unit test for the match and replace logic.
 *
 * @group G2
 */
class MatcherTest extends UnitTestCase {

  /**
   * Data provider for testHandleSource.
   *
   * @return array[]
   *   Test data.
   */
  public function providerHandle(): array {
    $res = [
      "multiple cases" => [
        ['foo', 'bar'],
        '<p>foo: In the foo, the <a href="/foo/bar">foo bar</a> is the bar',
        // Notice how the '<p>' is closed because we normalize the string.
        '<p><dfn>foo</dfn>: In the <dfn>foo</dfn>, the <a href="/foo/bar">foo bar</a> is the <dfn>bar</dfn></p>',
      ],
      "empty input" => [["foo"], "", ""],
      "whitespace input" => [["foo"], "  ", "  "],
      "missing closing element" => [["foo"], "<p> ", "<p> </p>"],
      "forbidden script" => [
        ["foo"],
        '<script>the foo bar</script>',
        '<script>the foo bar</script>',
      ],
      "forbidden style" => [
        ["foo"],
        '<style>the foo bar</style>',
        '<style>the foo bar</style>',
      ],
      "forbidden a" => [
        ["foo"],
        '<a href="zog">the foo bar</a>',
        '<a href="zog">the foo bar</a>',
      ],
      "existing dfn" => [
        ["foo"],
        'some <dfn>foo</dfn> is not a foo.',
        'some <dfn>foo</dfn> is not a <dfn>foo</dfn>.',
      ],
      "allowed em wrapper" => [
        ["foo"],
        'the <em>foo</em> bar',
        'the <em><dfn>foo</dfn></em> bar',
      ],
      "no match" => [
        ['baz'],
        '<p>no match: In the foo, the <a href="/foo/bar">foo bar</a> is the bar',
        '<p>no match: In the foo, the <a href="/foo/bar">foo bar</a> is the bar</p>',
      ],
      "match in stop list" => [
        ["no"],
        'This is no match',
        'This is no match',
      ],
    ];
    return $res;
  }

  /**
   * Test handleSource().
   *
   * @dataProvider providerHandle
   */
  public function testHandleSource(array $patterns, string $input, string $expected) {
    $msm = new MultiStringMatcher($patterns);
    $stopList = ['no'];
    $actual = Matcher::handleSource($input, $msm, $stopList);
    $this->assertEquals($expected, $actual);
  }

}
